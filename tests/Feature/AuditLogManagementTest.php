<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\UserManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuditLogManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_audit_screen_requires_authentication_and_audit_permission(): void
    {
        $this->get(route('admin.audit.index'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.audit.index'))->assertForbidden();
    }

    public function test_authorized_user_can_filter_logs_without_exposing_prohibited_metadata(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'name' => 'Auditor autorizado']);
        $target = User::factory()->create(['role' => 'editor', 'name' => 'Usuario objetivo']);
        $this->travelTo(now()->startOfDay()->addHours(10));

        app(AuditLogger::class)->record('role_changed', $target, [
            'from' => 'editor',
            'to' => 'author',
            'password' => 'do-not-store-password',
            'otp' => '123456',
            'smtp_password' => 'do-not-store-smtp-password',
            'api_key' => 'do-not-store-api-key',
            'token' => 'do-not-store-token',
            'cookie' => 'do-not-store-cookie',
            'session_id' => 'do-not-store-session',
        ], actorId: $admin->id);

        $log = AuditLog::query()->firstOrFail();
        $this->assertSame(['from' => 'editor', 'to' => 'author'], $log->metadata);

        $this->actingAs($admin)->get(route('admin.audit.index', [
            'user_id' => $admin->id,
            'event' => 'role_changed',
            'model' => User::class,
            'from' => now()->toDateString(),
            'to' => now()->toDateString(),
        ]))->assertOk()
            ->assertSee('Auditor autorizado')
            ->assertSee('#'.$target->id)
            ->assertSee('Role changed')
            ->assertDontSee('do-not-store-password')
            ->assertDontSee('123456')
            ->assertDontSee('do-not-store-smtp-password')
            ->assertDontSee('do-not-store-api-key')
            ->assertDontSee('do-not-store-token')
            ->assertDontSee('do-not-store-cookie')
            ->assertDontSee('do-not-store-session');
    }

    public function test_audit_routes_are_read_only(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));

        $this->post('/admin/audit')->assertMethodNotAllowed();
        $this->put('/admin/audit')->assertMethodNotAllowed();
        $this->delete('/admin/audit')->assertMethodNotAllowed();
    }

    public function test_login_logout_and_failed_login_events_are_recorded_without_credentials(): void
    {
        $user = User::factory()->create(['email' => 'audit-login@example.test', 'password' => 'ValidPassword123']);

        $this->from(route('login'))->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertRedirect(route('login'));
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'ValidPassword123'])
            ->assertRedirect(route('admin.dashboard'));
        $this->post(route('logout'))->assertRedirect(route('home'));

        $this->assertDatabaseHas('audit_logs', ['event' => 'login_failed', 'user_id' => null]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'login', 'user_id' => $user->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'logout', 'user_id' => $user->id]);

        $serializedLogs = DB::table('audit_logs')->get()->toJson();
        $this->assertStringNotContainsString('wrong-password', $serializedLogs);
        $this->assertStringNotContainsString('ValidPassword123', $serializedLogs);
        $this->assertStringNotContainsString($user->email, $serializedLogs);
    }

    public function test_user_creation_update_and_role_changes_are_logged(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin);

        $manager = app(UserManager::class);
        $created = $manager->create([
            'name' => 'Cuenta auditada',
            'email' => 'audited-user@example.test',
            'password' => 'DoNotLogThisPassword123',
            'role' => 'editor',
            'status' => 'active',
        ]);
        $manager->update($created, [
            'name' => 'Cuenta auditada editada',
            'email' => 'audited-user@example.test',
            'password' => null,
            'role' => 'author',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'event' => 'user_created',
            'model_type' => User::class,
            'model_id' => $created->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'event' => 'user_updated',
            'model_id' => $created->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'event' => 'role_changed',
            'model_id' => $created->id,
            'metadata' => json_encode(['from' => 'editor', 'to' => 'author']),
        ]);

        $this->assertStringNotContainsString('DoNotLogThisPassword123', DB::table('audit_logs')->get()->toJson());
    }

    public function test_banner_post_general_settings_and_smtp_changes_are_logged(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin);

        $bannerData = [
            'title' => 'Audit hero',
            'subtitle' => 'A safe banner subtitle',
            'image' => UploadedFile::fake()->createWithContent(
                'hero.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j3ioAAAAASUVORK5CYII=', true) ?: '',
            ),
            'image_alt' => 'Hero image',
            'button_text' => null,
            'button_url' => null,
            'position' => 1,
            'starts_at' => null,
            'ends_at' => null,
            'is_active' => '1',
        ];
        $this->post(route('admin.banners.store'), $bannerData)->assertRedirect(route('admin.banners.index'));
        $banner = Banner::query()->firstOrFail();
        $this->put(route('admin.banners.update', $banner), [...$bannerData, 'image' => null, 'title' => 'Updated audit hero'])
            ->assertRedirect(route('admin.banners.index'));

        $category = Category::query()->create([
            'name' => 'Audit category', 'slug' => 'audit-category', 'description' => null, 'is_active' => true,
        ]);
        $postData = [
            'title' => 'Audit article',
            'excerpt' => 'Safe excerpt',
            'content' => 'Safe content',
            'category_id' => $category->id,
            'status' => 'draft',
            'published_at' => null,
            'seo_title' => null,
            'meta_description' => null,
        ];
        $this->post(route('admin.posts.store'), $postData)->assertRedirect(route('admin.posts.index'));
        $post = Post::query()->firstOrFail();
        $this->put(route('admin.posts.update', $post), [...$postData, 'title' => 'Updated audit article'])
            ->assertRedirect(route('admin.posts.index'));

        $this->put(route('admin.settings.update'), [
            'site_name' => 'Audit studio', 'remove_logo' => '0', 'remove_favicon' => '0',
            'contact_email' => 'contact@example.test', 'phone' => null, 'address' => null,
            'description' => null, 'footer_text' => null, 'locale' => 'es',
            'presentation_timezone' => 'America/Bogota',
        ])->assertRedirect(route('admin.settings.edit'));

        $this->put(route('admin.seo.update'), [
            'site_title' => 'Audit studio', 'default_meta_description' => 'A safe audit test description.',
            'default_og_image' => null, 'robots_index' => '1', 'robots_follow' => '1',
        ])->assertRedirect(route('admin.seo.edit'));

        $this->put(route('admin.settings.mail.update'), [
            'mailer' => 'smtp', 'host' => null, 'port' => null, 'encryption' => null,
            'username' => null, 'password' => '', 'from_address' => null, 'from_name' => null,
            'is_active' => '0',
        ])->assertRedirect(route('admin.settings.mail.edit'));

        foreach (['banner_created', 'banner_updated', 'post_created', 'post_updated', 'settings_updated', 'smtp_updated'] as $event) {
            $this->assertDatabaseHas('audit_logs', ['user_id' => $admin->id, 'event' => $event]);
        }
        $this->assertDatabaseHas('audit_logs', ['event' => 'banner_created', 'model_type' => Banner::class, 'model_id' => $banner->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'post_created', 'model_type' => Post::class, 'model_id' => $post->id]);
        $this->assertSame(2, DB::table('audit_logs')->where('event', 'settings_updated')->count());
    }
}
