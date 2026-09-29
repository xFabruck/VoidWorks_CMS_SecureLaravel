<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_guest_cannot_access_post_administration(): void
    {
        $this->get(route('admin.posts.index'))->assertRedirect(route('login'));
        $this->get(route('admin.posts.create'))->assertRedirect(route('login'));
        $this->post(route('admin.posts.store'), [])->assertRedirect(route('login'));
    }

    public function test_author_can_create_and_update_post_with_category_and_author_relations(): void
    {
        $author = User::factory()->create();
        $category = $this->category();

        $this->actingAs($author)->post(route('admin.posts.store'), $this->formData($category))
            ->assertRedirect(route('admin.posts.index'))->assertSessionHas('status');
        $post = Post::query()->firstOrFail();

        $this->assertSame('test-news', $post->slug);
        $this->assertSame($author->id, $post->author_id);
        $this->assertSame($category->id, $post->category_id);
        $this->assertSame($category->id, $post->category->id);
        $this->assertSame($author->id, $post->author->id);
        $this->assertSame('draft', $post->status);

        $this->put(route('admin.posts.update', $post), [...$this->formData($category), 'title' => 'Revised news', 'slug' => 'revised-news'])
            ->assertRedirect(route('admin.posts.index'));
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Revised news', 'slug' => 'revised-news']);
    }

    public function test_slug_is_unique_and_can_be_omitted_for_automatic_generation(): void
    {
        $user = User::factory()->create();
        $category = $this->category();
        $this->actingAs($user)->post(route('admin.posts.store'), $this->formData($category))->assertRedirect();
        $first = Post::query()->firstOrFail();

        $this->post(route('admin.posts.store'), $this->formData($category))->assertRedirect();
        $this->assertDatabaseHas('posts', ['slug' => 'test-news-2']);

        $this->from(route('admin.posts.create'))
            ->post(route('admin.posts.store'), [...$this->formData($category), 'slug' => $first->slug])
            ->assertRedirect(route('admin.posts.create'))->assertSessionHasErrors('slug');
    }

    public function test_non_author_cannot_preview_edit_publish_archive_or_delete(): void
    {
        $post = $this->postRecord(User::factory()->create(), $this->category());
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.posts.preview', $post))->assertForbidden();
        $this->get(route('admin.posts.edit', $post))->assertForbidden();
        $this->put(route('admin.posts.update', $post), [])->assertForbidden();
        $this->patch(route('admin.posts.publish', $post))->assertForbidden();
        $this->patch(route('admin.posts.unpublish', $post))->assertForbidden();
        $this->patch(route('admin.posts.archive', $post))->assertForbidden();
        $this->delete(route('admin.posts.destroy', $post))->assertForbidden();
    }

    public function test_scheduled_and_future_posts_are_hidden_until_publishing_command_runs(): void
    {
        $author = User::factory()->create();
        $category = $this->category();
        $future = now()->addMinutes(2);
        $this->actingAs($author)->post(route('admin.posts.store'), $this->formData($category, status: 'scheduled', publishedAt: $future->format('Y-m-d\TH:i')))
            ->assertRedirect(route('admin.posts.index'));
        $scheduled = Post::query()->firstOrFail();
        $this->assertSame('scheduled', $scheduled->status);

        Post::query()->create($this->attributes($author, $category, title: 'Future published', status: 'published', publishedAt: now()->addDay()));
        Post::query()->create($this->attributes($author, $category, title: 'Archived news', status: 'archived'));

        $this->get(route('news.index'))->assertOk()->assertDontSee('Test News')->assertDontSee('Future published')->assertDontSee('Archived news');
        $this->get(route('news.show', $scheduled->slug))->assertNotFound();

        $this->travelTo($future->addMinute());
        Artisan::call('posts:publish-due');
        $scheduled->refresh();

        $this->assertSame('published', $scheduled->status);
        $this->get(route('news.index'))->assertOk()->assertSee('Test News');
        $this->get(route('news.show', $scheduled->slug))->assertOk()->assertSee('Test article content.');
    }

    public function test_published_post_with_future_date_is_automatically_scheduled(): void
    {
        $author = User::factory()->create();
        $category = $this->category();
        $future = now()->addDay()->format('Y-m-d\TH:i');

        $this->actingAs($author)->post(route('admin.posts.store'), $this->formData($category, status: 'published', publishedAt: $future))
            ->assertRedirect(route('admin.posts.index'));

        $this->assertDatabaseHas('posts', ['slug' => 'test-news', 'status' => 'scheduled']);
        $this->get(route('news.index'))->assertDontSee('Test News');
    }

    public function test_unpublish_and_archive_actions_remove_post_from_public_pages(): void
    {
        $author = User::factory()->create();
        $category = $this->category();
        $post = $this->postRecord($author, $category, status: 'published', publishedAt: now()->subMinute());

        $this->actingAs($author)->patch(route('admin.posts.unpublish', $post))->assertRedirect();
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'status' => 'draft', 'published_at' => null]);
        $this->get(route('news.show', $post->slug))->assertNotFound();

        $this->patch(route('admin.posts.publish', $post))->assertRedirect();
        $this->patch(route('admin.posts.archive', $post))->assertRedirect();
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'status' => 'archived']);
        $this->get(route('news.show', $post->slug))->assertNotFound();
    }

    public function test_featured_image_is_validated_stored_with_generated_name_and_cleaned_on_replace(): void
    {
        $author = User::factory()->create();
        $category = $this->category();
        $this->actingAs($author)->post(route('admin.posts.store'), $this->formData($category, image: $this->fakePng('client-name.png')))->assertRedirect();
        $post = Post::query()->firstOrFail();
        $oldImage = $post->featured_image;

        $this->assertNotSame('client-name.png', basename((string) $oldImage));
        Storage::disk('public')->assertExists((string) $oldImage);

        $this->put(route('admin.posts.update', $post), $this->formData($category, image: $this->fakePng('replacement.png')))->assertRedirect();
        $post->refresh();
        Storage::disk('public')->assertMissing((string) $oldImage);
        Storage::disk('public')->assertExists((string) $post->featured_image);
    }

    public function test_invalid_image_and_invalid_or_deleted_category_are_rejected(): void
    {
        $user = User::factory()->create();
        $category = $this->category();
        $this->actingAs($user)->from(route('admin.posts.create'))
            ->post(route('admin.posts.store'), $this->formData($category, image: UploadedFile::fake()->createWithContent('unsafe.svg', '<svg/>')))
            ->assertRedirect(route('admin.posts.create'))->assertSessionHasErrors('featured_image');

        $category->delete();
        $this->from(route('admin.posts.create'))
            ->post(route('admin.posts.store'), $this->formData($category))
            ->assertRedirect(route('admin.posts.create'))->assertSessionHasErrors('category_id');

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_category_foreign_key_restricts_physical_deletion_while_posts_reference_it(): void
    {
        $category = $this->category();
        $post = $this->postRecord(User::factory()->create(), $category);

        $this->expectException(QueryException::class);
        $category->forceDelete();
    }

    public function test_public_pages_are_paginated_and_content_is_escaped(): void
    {
        $author = User::factory()->create();
        $category = $this->category();
        $this->postRecord($author, $category, title: 'Published news 10', status: 'published', publishedAt: now()->subDay());
        $this->postRecord($author, $category, title: '<script>alert(1)</script>', status: 'published', publishedAt: now()->subDay(), content: '<img src=x onerror=alert(1)>');
        for ($number = 2; $number <= 9; $number++) {
            $this->postRecord($author, $category, title: "Published news {$number}", status: 'published', publishedAt: now()->subDay());
        }

        $this->get(route('news.index'))->assertOk()->assertDontSee('Published news 10');
        $this->get(route('news.index', ['page' => 2]))->assertOk()->assertSee('Published news 10');
        $this->get(route('news.show', str('<script>alert(1)</script>')->slug()))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
        $this->get(route('news.show', 'missing-post'))->assertNotFound();
    }

    private function category(): Category
    {
        return Category::query()->create([
            'name' => 'News category',
            'slug' => 'news-category',
            'description' => 'For test articles.',
            'is_active' => true,
        ]);
    }

    private function postRecord(User $author, Category $category, string $title = 'Test News', string $status = 'draft', mixed $publishedAt = null, string $content = 'Test article content.'): Post
    {
        return Post::query()->create($this->attributes($author, $category, $title, $status, $publishedAt, $content));
    }

    /** @return array<string, mixed> */
    private function attributes(User $author, Category $category, string $title = 'Test News', string $status = 'draft', mixed $publishedAt = null, string $content = 'Test article content.'): array
    {
        return [
            'title' => $title,
            'slug' => str($title)->slug(),
            'excerpt' => 'A short test summary.',
            'content' => $content,
            'category_id' => $category->id,
            'author_id' => $author->id,
            'status' => $status,
            'published_at' => $publishedAt,
        ];
    }

    /** @return array<string, mixed> */
    private function formData(Category $category, ?UploadedFile $image = null, string $status = 'draft', ?string $publishedAt = null): array
    {
        return [
            'title' => 'Test News',
            'excerpt' => 'A short test summary.',
            'content' => 'Test article content.',
            'featured_image' => $image,
            'category_id' => $category->id,
            'status' => $status,
            'published_at' => $publishedAt,
            'seo_title' => 'Test SEO title',
            'meta_description' => 'Test SEO description.',
        ];
    }

    private function fakePng(string $name = 'featured.png'): UploadedFile
    {
        $content = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j3ioAAAAASUVORK5CYII=', true);

        return UploadedFile::fake()->createWithContent($name, $content ?: '');
    }
}
