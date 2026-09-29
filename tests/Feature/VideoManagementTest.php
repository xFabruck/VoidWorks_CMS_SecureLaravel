<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_cannot_access_video_administration(): void
    {
        $this->get(route('admin.videos.index'))->assertRedirect(route('login'));
        $this->get(route('admin.videos.create'))->assertRedirect(route('login'));
        $this->post(route('admin.videos.store'), [])->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_youtube_video_with_generated_embed_and_thumbnail(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.videos.store'), $this->videoData())
            ->assertRedirect(route('admin.videos.index'))
            ->assertSessionHas('status');

        $video = Video::query()->firstOrFail();
        $this->assertSame('youtube', $video->provider);
        $this->assertSame('a1B2c3D4e5F', $video->video_id);
        $this->assertSame('https://www.youtube.com/watch?v=a1B2c3D4e5F', $video->video_url);
        $this->assertSame('https://i.ytimg.com/vi/a1B2c3D4e5F/hqdefault.jpg', $video->thumbnail);
        $this->assertSame('https://www.youtube-nocookie.com/embed/a1B2c3D4e5F', $video->embed_url);
        $this->assertDatabaseHas('videos', ['title' => 'Gameplay teaser', 'position' => 2, 'is_active' => 1]);
    }

    public function test_vimeo_share_and_player_urls_are_canonicalized(): void
    {
        $data = [...$this->videoData(), 'provider' => 'vimeo', 'video_url' => 'https://player.vimeo.com/video/987654321', 'thumbnail' => 'https://i.vimeocdn.com/video/123456_640.jpg'];

        $this->actingAs(User::factory()->create())->post(route('admin.videos.store'), $data)->assertRedirect();

        $video = Video::query()->firstOrFail();
        $this->assertSame('987654321', $video->video_id);
        $this->assertSame('https://vimeo.com/987654321', $video->video_url);
        $this->assertSame('https://player.vimeo.com/video/987654321', $video->embed_url);
        $this->assertSame('https://i.vimeocdn.com/video/123456_640.jpg', $video->thumbnail);
    }

    public function test_private_vimeo_hashes_are_not_silently_discarded(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.videos.store'), [...$this->videoData(), 'provider' => 'vimeo', 'video_url' => 'https://vimeo.com/987654321?h=private-hash'])
            ->assertSessionHasErrors('video_url');

        $this->assertDatabaseCount('videos', 0);
    }

    public function test_arbitrary_domains_iframe_html_and_provider_mismatch_are_rejected(): void
    {
        $this->actingAs(User::factory()->create());
        $invalidUrls = [
            'https://youtube.com.attacker.test/watch?v=a1B2c3D4e5F',
            'https://youtube.com@attacker.test/watch?v=a1B2c3D4e5F',
            'http://youtube.com/watch?v=a1B2c3D4e5F',
            '<iframe src="https://youtube.com/embed/a1B2c3D4e5F"></iframe>',
            'javascript:alert(1)',
            'https://youtube.com/watch?v=too-short',
        ];

        foreach ($invalidUrls as $url) {
            $this->from(route('admin.videos.create'))
                ->post(route('admin.videos.store'), [...$this->videoData(), 'video_url' => $url])
                ->assertRedirect(route('admin.videos.create'))
                ->assertSessionHasErrors('video_url');
        }

        $this->post(route('admin.videos.store'), [...$this->videoData(), 'provider' => 'vimeo'])
            ->assertSessionHasErrors('video_url');
        $this->assertDatabaseCount('videos', 0);
    }

    public function test_thumbnail_must_use_the_selected_provider_cdn(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.videos.store'), [...$this->videoData(), 'thumbnail' => 'https://attacker.test/tracker.jpg'])
            ->assertSessionHasErrors('thumbnail');

        $this->assertDatabaseCount('videos', 0);
    }

    public function test_user_can_edit_toggle_and_delete_video(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post(route('admin.videos.store'), $this->videoData())->assertRedirect();
        $video = Video::query()->firstOrFail();

        $this->put(route('admin.videos.update', $video), [...$this->videoData(), 'title' => 'Updated title', 'video_url' => 'https://youtu.be/z9Y8x7W6v5U'])
            ->assertRedirect(route('admin.videos.index'));
        $video->refresh();
        $this->assertSame('Updated title', $video->title);
        $this->assertSame('z9Y8x7W6v5U', $video->video_id);

        $this->patch(route('admin.videos.toggle', $video))->assertRedirect();
        $this->assertFalse($video->refresh()->is_active);

        $this->delete(route('admin.videos.destroy', $video))->assertRedirect()->assertSessionHas('status');
        $this->assertDatabaseMissing('videos', ['id' => $video->id]);
    }

    public function test_public_gallery_shows_only_active_videos_in_order_and_escapes_text(): void
    {
        $inactive = $this->videoRecord('Inactive', 'b2C3d4E5f6G', false, 0);
        $second = $this->videoRecord('Second video', 'c3D4e5F6g7H', true, 2);
        $first = $this->videoRecord('<script>alert(1)</script>', 'a1B2c3D4e5F', true, 1);
        $first->update(['description' => '<img src=x onerror=alert(1)>']);

        $this->assertDatabaseHas('videos', ['id' => $inactive->id]);
        $this->get(route('videos.index'))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertSee('youtube-nocookie.com/embed/a1B2c3D4e5F', false)
            ->assertDontSee('Inactive')
            ->assertDontSee('b2C3d4E5f6G')
            ->assertSeeInOrder(['<script>alert(1)</script>', 'Second video']);
    }

    private function videoData(): array
    {
        return [
            'title' => 'Gameplay teaser',
            'description' => 'First public video.',
            'provider' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=a1B2c3D4e5F&t=20',
            'thumbnail' => '',
            'position' => 2,
            'is_active' => '1',
        ];
    }

    private function videoRecord(string $title, string $id, bool $active, int $position): Video
    {
        return Video::query()->create([
            'title' => $title,
            'description' => 'Description',
            'provider' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v='.$id,
            'video_id' => $id,
            'thumbnail' => 'https://i.ytimg.com/vi/'.$id.'/hqdefault.jpg',
            'position' => $position,
            'is_active' => $active,
        ]);
    }
}
