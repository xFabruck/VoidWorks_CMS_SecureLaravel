<?php

namespace Tests\Feature;

use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialLinkManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_cannot_access_social_link_administration(): void
    {
        $this->get(route('admin.social-links.index'))->assertRedirect(route('login'));
        $this->get(route('admin.social-links.create'))->assertRedirect(route('login'));
        $this->post(route('admin.social-links.store'), [])->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_a_social_link_with_derived_icon(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.social-links.store'), $this->data())
            ->assertRedirect(route('admin.social-links.index'))->assertSessionHas('status');

        $link = SocialLink::query()->firstOrFail();
        $this->assertSame('youtube', $link->network);
        $this->assertSame('youtube', $link->icon);
        $this->assertSame('https://www.youtube.com/@voidworks', $link->url);
        $this->assertSame(2, $link->position);
        $this->assertTrue($link->is_active);
    }

    public function test_other_network_uses_globe_icon(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.social-links.store'), $this->data([
                'network' => 'other',
                'url' => 'https://studio.example.test/profile',
            ]))->assertRedirect();

        $this->assertDatabaseHas('social_links', ['network' => 'other', 'icon' => 'globe']);
    }

    public function test_urls_must_be_https_and_match_the_selected_platform(): void
    {
        $this->actingAs(User::factory()->create());
        $invalidUrls = [
            'http://instagram.com/voidworks',
            'https://instagram.com.attacker.test/voidworks',
            'https://instagram.com@attacker.test/voidworks',
            'javascript:alert(1)',
        ];

        foreach ($invalidUrls as $url) {
            $this->from(route('admin.social-links.create'))
                ->post(route('admin.social-links.store'), $this->data(['network' => 'instagram', 'url' => $url]))
                ->assertRedirect(route('admin.social-links.create'))->assertSessionHasErrors('url');
        }

        $this->assertDatabaseCount('social_links', 0);
    }

    public function test_user_can_edit_reorder_toggle_and_delete_link(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('admin.social-links.store'), $this->data());
        $link = SocialLink::query()->firstOrFail();

        $this->put(route('admin.social-links.update', $link), $this->data([
            'network' => 'instagram',
            'url' => 'https://instagram.com/voidworks',
            'position' => 8,
            'is_active' => '0',
        ]))->assertRedirect(route('admin.social-links.index'));
        $this->assertDatabaseHas('social_links', ['id' => $link->id, 'network' => 'instagram', 'icon' => 'instagram', 'position' => 8, 'is_active' => false]);

        $this->patch(route('admin.social-links.toggle', $link))->assertRedirect();
        $this->assertDatabaseHas('social_links', ['id' => $link->id, 'is_active' => true]);

        $this->delete(route('admin.social-links.destroy', $link))->assertRedirect()->assertSessionHas('status');
        $this->assertDatabaseMissing('social_links', ['id' => $link->id]);
    }

    public function test_public_layout_renders_only_active_links_from_database_in_position_order_in_footer(): void
    {
        config(['social.location' => 'footer']);
        $this->createLink('instagram', 'https://instagram.com/voidworks', true, 3);
        $this->createLink('youtube', 'https://youtube.com/@voidworks', true, 1);
        $this->createLink('facebook', 'https://facebook.com/voidworks', false, 0);

        $this->get(route('home'))->assertOk()
            ->assertSeeInOrder(['YouTube (abre en una pestaña nueva)', 'Instagram (abre en una pestaña nueva)'])
            ->assertDontSee('facebook.com/voidworks')
            ->assertSee('href="https://youtube.com/@voidworks"', false)
            ->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_public_layout_can_render_social_links_in_navbar_from_configuration(): void
    {
        config(['social.location' => 'navbar']);
        $this->createLink('linkedin', 'https://linkedin.com/company/voidworks', true, 0);

        $this->get(route('home'))->assertOk()
            ->assertSee('LinkedIn (abre en una pestaña nueva)', false)
            ->assertSee('href="https://linkedin.com/company/voidworks"', false);
    }

    /** @return array<string, string> */
    private function data(array $overrides = []): array
    {
        return array_replace([
            'network' => 'youtube',
            'url' => 'https://www.youtube.com/@voidworks',
            'position' => '2',
            'is_active' => '1',
        ], $overrides);
    }

    private function createLink(string $network, string $url, bool $active, int $position): SocialLink
    {
        $link = new SocialLink;
        $link->network = $network;
        $link->url = $url;
        $link->icon = $network;
        $link->position = $position;
        $link->is_active = $active;
        $link->save();

        return $link;
    }
}
