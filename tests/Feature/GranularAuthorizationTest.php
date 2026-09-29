<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class GranularAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_permission_catalog_matches_the_supported_abilities(): void
    {
        $catalog = config('permissions.catalog');
        $this->assertSame([
            'banners.view', 'banners.create', 'banners.update', 'banners.delete',
            'services.view', 'services.create', 'services.update', 'services.delete',
            'posts.view', 'posts.create', 'posts.update', 'posts.delete', 'posts.publish',
            'media.view', 'media.upload', 'media.delete',
            'users.view', 'users.create', 'users.update', 'users.delete',
            'settings.view', 'settings.update', 'security.view', 'audit.view',
        ], $catalog);

        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin']);
        $editor = User::factory()->create(['role' => 'editor']);
        $author = User::factory()->create(['role' => 'author']);

        $this->assertTrue(Gate::forUser($superAdmin)->allows('audit.view'));
        $this->assertTrue(Gate::forUser($admin)->allows('settings.update'));
        $this->assertFalse(Gate::forUser($admin)->allows('security.view'));
        $this->assertTrue(Gate::forUser($editor)->allows('posts.publish'));
        $this->assertFalse(Gate::forUser($editor)->allows('users.view'));
        $this->assertTrue(Gate::forUser($author)->allows('posts.create'));
        $this->assertFalse(Gate::forUser($author)->allows('posts.publish'));
        $this->assertFalse(Gate::forUser($author)->allows('banners.view'));
    }

    public function test_direct_urls_are_forbidden_without_the_required_permission(): void
    {
        $author = User::factory()->create(['role' => 'author']);

        $this->actingAs($author)->get('/admin/banners')->assertForbidden();
        $this->get('/admin/categories')->assertForbidden();
        $this->get('/admin/seo')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
    }

    public function test_admin_role_is_authorized_for_permitted_admin_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.banners.index'))->assertOk();
        $this->get(route('admin.settings.edit'))->assertOk();
    }

    public function test_editor_can_manage_content_but_cannot_access_settings_or_user_admin(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        $this->actingAs($editor)->get('/admin/banners')->assertOk();
        $this->get('/admin/services')->assertOk();
        $this->get('/admin/posts')->assertOk();
        $this->get('/admin/media')->assertOk();
        $this->get('/admin/seo')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
    }

    public function test_author_can_edit_only_their_own_posts_and_cannot_publish(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $otherAuthor = User::factory()->create(['role' => 'author']);
        $category = Category::query()->create([
            'name' => 'Noticias', 'slug' => 'noticias', 'description' => null, 'is_active' => true,
        ]);
        $post = Post::query()->create([
            'title' => 'Borrador', 'slug' => 'borrador-prueba', 'content' => 'Contenido',
            'category_id' => $category->id, 'author_id' => $otherAuthor->id, 'status' => 'draft',
        ]);

        $this->actingAs($author)->get(route('admin.posts.create'))->assertOk();
        $this->get(route('admin.posts.edit', $post))->assertForbidden();
        $this->patch(route('admin.posts.publish', $post))->assertForbidden();
    }

    public function test_user_administration_cannot_escalate_admin_to_super_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Elevación no permitida',
            'email' => 'escalation@example.test',
            'password' => 'StrongPassword5678',
            'password_confirmation' => 'StrongPassword5678',
            'role' => 'super_admin',
            'status' => 'active',
        ])->assertForbidden();
    }
}
