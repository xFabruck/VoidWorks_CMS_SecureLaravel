<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Media;
use App\Models\Post;
use App\Models\Service;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_superadmin_dashboard_uses_real_counts_and_recent_records(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'name' => 'Dashboard Admin']);
        $author = User::factory()->create(['role' => 'author']);

        Banner::query()->create([
            'title' => 'Visible dashboard banner', 'subtitle' => null, 'image' => 'banners/visible.webp',
            'image_alt' => 'Visible', 'button_text' => null, 'button_url' => null, 'position' => 1,
            'is_active' => true, 'created_by' => $admin->id,
        ]);
        Banner::query()->create([
            'title' => 'Inactive dashboard banner', 'subtitle' => null, 'image' => 'banners/inactive.webp',
            'image_alt' => 'Inactive', 'button_text' => null, 'button_url' => null, 'position' => 2,
            'is_active' => false, 'created_by' => $admin->id,
        ]);
        Service::query()->create([
            'name' => 'Visible service', 'slug' => 'visible-service', 'short_description' => 'Short',
            'description' => 'Description', 'icon' => null, 'image' => null, 'button_text' => null,
            'button_url' => null, 'position' => 1, 'is_active' => true, 'created_by' => $admin->id,
        ]);
        Service::query()->create([
            'name' => 'Inactive service', 'slug' => 'inactive-service', 'short_description' => 'Short',
            'description' => 'Description', 'icon' => null, 'image' => null, 'button_text' => null,
            'button_url' => null, 'position' => 2, 'is_active' => false, 'created_by' => $admin->id,
        ]);

        $category = Category::query()->create([
            'name' => 'Dashboard category', 'slug' => 'dashboard-category', 'description' => null, 'is_active' => true,
        ]);
        Post::query()->create([
            'title' => 'Dashboard draft', 'slug' => 'dashboard-draft', 'excerpt' => null, 'content' => 'Draft',
            'category_id' => $category->id, 'author_id' => $admin->id, 'status' => 'draft',
        ]);
        Post::query()->create([
            'title' => 'Dashboard published', 'slug' => 'dashboard-published', 'excerpt' => null, 'content' => 'Published',
            'category_id' => $category->id, 'author_id' => $admin->id, 'status' => 'published', 'published_at' => now()->subDay(),
        ]);
        Post::query()->create([
            'title' => 'Other author article', 'slug' => 'other-author-article', 'excerpt' => null, 'content' => 'Other',
            'category_id' => $category->id, 'author_id' => $author->id, 'status' => 'draft',
        ]);

        Media::query()->create([
            'name' => 'Dashboard asset', 'file_name' => 'asset.pdf', 'path' => 'media/asset.pdf',
            'disk' => 'local', 'mime_type' => 'application/pdf', 'extension' => 'pdf', 'size' => 1024,
            'type' => 'document', 'uploaded_by' => $admin->id,
        ]);
        ContactMessage::query()->create([
            'name' => 'Contact sender', 'email' => 'sender@example.test', 'subject' => 'Pending dashboard request',
            'message' => 'Message body', 'status' => 'new',
        ]);
        app(AuditLogger::class)->record('login', $admin, actorId: $admin->id);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $response->assertSee('Banners activos')->assertSee('Servicios activos')
            ->assertSee('Publicaciones')->assertSee('Borradores')->assertSee('Usuarios')
            ->assertSee('Mensajes nuevos')->assertSee('Multimedia')
            ->assertSee('Actividad reciente')->assertSee('Últimos accesos')
            ->assertSee('Publicaciones recientes')->assertSee('Mensajes pendientes')
            ->assertSee('Dashboard draft')->assertSee('Dashboard published')
            ->assertSee('Pending dashboard request');

        $this->assertSame(1, $this->metricValue($response->getContent(), 'Banners activos'));
        $this->assertSame(1, $this->metricValue($response->getContent(), 'Servicios activos'));
        $this->assertSame(3, $this->metricValue($response->getContent(), 'Publicaciones'));
        $this->assertSame(2, $this->metricValue($response->getContent(), 'Borradores'));
        $this->assertSame(2, $this->metricValue($response->getContent(), 'Usuarios'));
        $this->assertSame(1, $this->metricValue($response->getContent(), 'Mensajes nuevos'));
        $this->assertSame(1, $this->metricValue($response->getContent(), 'Multimedia'));
    }

    public function test_dashboard_hides_unauthorized_metrics_and_only_shows_authors_own_posts(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $otherAuthor = User::factory()->create(['role' => 'author']);
        $category = Category::query()->create([
            'name' => 'Private author category', 'slug' => 'private-author-category', 'description' => null, 'is_active' => true,
        ]);
        Post::query()->create([
            'title' => 'My dashboard draft', 'slug' => 'my-dashboard-draft', 'excerpt' => null, 'content' => 'Draft',
            'category_id' => $category->id, 'author_id' => $author->id, 'status' => 'draft',
        ]);
        Post::query()->create([
            'title' => 'Private article of another author', 'slug' => 'private-article-other-author', 'excerpt' => null, 'content' => 'Do not expose',
            'category_id' => $category->id, 'author_id' => $otherAuthor->id, 'status' => 'published', 'published_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($author)->get(route('admin.dashboard'))->assertOk()
            ->assertSee('Publicaciones recientes')
            ->assertSee('My dashboard draft')
            ->assertDontSee('Private article of another author')
            ->assertDontSee('Banners activos')
            ->assertDontSee('Usuarios')
            ->assertDontSee('Mensajes pendientes')
            ->assertDontSee('Actividad reciente')
            ->assertDontSee('Últimos accesos');

        $this->assertSame(1, $this->metricValue($response->getContent(), 'Publicaciones'));
        $this->assertSame(1, $this->metricValue($response->getContent(), 'Borradores'));
    }

    public function test_dashboard_requires_an_authenticated_active_user(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    private function metricValue(string $html, string $label): int
    {
        $pattern = '/<span class="text-sm text-white\/55">'.preg_quote($label, '/').'<\/span>.*?<p class="mt-4 font-display text-3xl font-semibold tabular-nums text-white">([0-9,]+)/s';
        $this->assertMatchesRegularExpression($pattern, $html);
        preg_match($pattern, $html, $matches);

        return (int) str_replace(',', '', $matches[1]);
    }
}
