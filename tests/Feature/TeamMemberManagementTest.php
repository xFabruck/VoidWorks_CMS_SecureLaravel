<?php

namespace Tests\Feature;

use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeamMemberManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
    }

    public function test_guest_cannot_access_team_administration_and_public_page_is_available(): void
    {
        $this->get(route('admin.team.index'))->assertRedirect(route('login'));
        $this->get(route('admin.team.create'))->assertRedirect(route('login'));
        $this->post(route('admin.team.store'), [])->assertRedirect(route('login'));
        $this->get(route('team.index'))->assertOk()->assertSee('Nuestro equipo');
    }

    public function test_member_contact_and_biography_are_private_until_explicitly_enabled(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.team.store'), $this->formData())
            ->assertRedirect(route('admin.team.index'));

        $member = TeamMember::query()->firstOrFail();
        $this->assertFalse($member->show_biography);
        $this->assertFalse($member->show_email);
        $this->assertFalse($member->show_phone);
        $this->assertFalse($member->show_linkedin_url);
        $this->assertFalse($member->show_photo);

        $this->get(route('team.index'))->assertOk()
            ->assertSee('Morgan Lee')
            ->assertSee('Creative Director')
            ->assertDontSee('Private biography')
            ->assertDontSee('morgan.private@example.test')
            ->assertDontSee('+1 555 0100')
            ->assertDontSee('linkedin.com/in/private-profile');
    }

    public function test_admin_can_choose_which_optional_fields_are_public(): void
    {
        $data = [...$this->formData(), 'show_biography' => '1', 'show_email' => '1', 'show_linkedin_url' => '1'];
        $this->actingAs(User::factory()->create())->post(route('admin.team.store'), $data)->assertRedirect();

        $this->get(route('team.index'))->assertOk()
            ->assertSee('Private biography')
            ->assertSee('mailto:morgan.private@example.test', false)
            ->assertSee('https://www.linkedin.com/in/private-profile', false)
            ->assertDontSee('tel:+15550100');
    }

    public function test_valid_photo_is_stored_with_generated_name_replaced_and_deleted_safely(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.team.store'), $this->formData($this->fakePng('user-photo.png')))
            ->assertRedirect(route('admin.team.index'));
        $member = TeamMember::query()->firstOrFail();
        $oldPhoto = $member->photo;

        $this->assertNotNull($oldPhoto);
        $this->assertNotSame('user-photo.png', basename($oldPhoto));
        Storage::disk('local')->assertExists($oldPhoto);

        $this->get(route('team.photo', $member))->assertNotFound();
        $member->update(['is_active' => true, 'show_photo' => true]);
        $this->get(route('team.photo', $member))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->actingAs(User::factory()->create())->get(route('admin.team.photo', $member))->assertOk();
        $member->update(['is_active' => false]);
        $this->get(route('team.photo', $member))->assertNotFound();

        $this->put(route('admin.team.update', $member), $this->formData($this->fakePng('replacement.png'), showPhoto: true))
            ->assertRedirect(route('admin.team.index'));
        $member->refresh();
        Storage::disk('local')->assertMissing($oldPhoto);
        Storage::disk('local')->assertExists((string) $member->photo);

        $this->delete(route('admin.team.destroy', $member))->assertRedirect(route('admin.team.index'));
        Storage::disk('local')->assertMissing((string) $member->photo);
        $this->assertDatabaseMissing('team_members', ['id' => $member->id]);
    }

    public function test_invalid_photo_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('admin.team.create'))
            ->post(route('admin.team.store'), $this->formData(UploadedFile::fake()->createWithContent('unsafe.svg', '<svg><script>alert(1)</script></svg>')))
            ->assertRedirect(route('admin.team.create'))
            ->assertSessionHasErrors('photo');

        $this->assertDatabaseCount('team_members', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_linkedin_url_must_use_https_official_domain_and_invalid_phone_is_rejected(): void
    {
        $this->actingAs(User::factory()->create());
        $this->from(route('admin.team.create'))
            ->post(route('admin.team.store'), [...$this->formData(), 'linkedin_url' => 'https://linkedin.com.attacker.test/in/profile'])
            ->assertRedirect(route('admin.team.create'))->assertSessionHasErrors('linkedin_url');

        $this->post(route('admin.team.store'), [...$this->formData(), 'linkedin_url' => 'https://www.linkedin.com/in/profile', 'phone' => 'javascript:alert(1)'])
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('team_members', 0);
    }

    public function test_admin_can_reorder_toggle_and_delete_member(): void
    {
        $this->actingAs(User::factory()->create())->post(route('admin.team.store'), $this->formData())->assertRedirect();
        $member = TeamMember::query()->firstOrFail();

        $this->put(route('admin.team.update', $member), [...$this->formData(), 'name' => 'Updated name', 'position_order' => 12])
            ->assertRedirect(route('admin.team.index'));
        $this->assertDatabaseHas('team_members', ['id' => $member->id, 'name' => 'Updated name', 'position_order' => 12]);

        $this->patch(route('admin.team.toggle', $member))->assertRedirect();
        $this->assertDatabaseHas('team_members', ['id' => $member->id, 'is_active' => false]);

        $this->get(route('team.index'))->assertDontSee('Updated name');
        $this->delete(route('admin.team.destroy', $member))->assertRedirect();
        $this->assertDatabaseMissing('team_members', ['id' => $member->id]);
    }

    public function test_public_page_shows_active_members_in_order_and_escapes_biography(): void
    {
        $this->memberRecord('Second member', active: true, order: 2, showBiography: true);
        $this->memberRecord('Inactive member', active: false, order: 0, showBiography: true);
        $this->memberRecord('<script>alert(1)</script>', active: true, order: 1, showBiography: true, biography: '<img src=x onerror=alert(1)>');

        $this->get(route('team.index'))->assertOk()
            ->assertSeeInOrder(['<script>alert(1)</script>', 'Second member'])
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false)
            ->assertDontSee('Inactive member');
    }

    /** @return array<string, mixed> */
    private function formData(?UploadedFile $photo = null, bool $showPhoto = false): array
    {
        return [
            'name' => 'Morgan Lee',
            'position' => 'Creative Director',
            'biography' => 'Private biography',
            'photo' => $photo,
            'email' => 'morgan.private@example.test',
            'phone' => '+1 555 0100',
            'linkedin_url' => 'https://www.linkedin.com/in/private-profile',
            'position_order' => 1,
            'is_active' => '1',
            'show_biography' => '0',
            'show_photo' => $showPhoto ? '1' : '0',
            'show_email' => '0',
            'show_phone' => '0',
            'show_linkedin_url' => '0',
        ];
    }

    private function memberRecord(string $name, bool $active = true, int $order = 1, bool $showBiography = false, string $biography = 'Biography'): TeamMember
    {
        return TeamMember::query()->create([
            'name' => $name,
            'position' => 'Designer',
            'biography' => $biography,
            'position_order' => $order,
            'is_active' => $active,
            'show_biography' => $showBiography,
            'show_photo' => false,
            'show_email' => false,
            'show_phone' => false,
            'show_linkedin_url' => false,
        ]);
    }

    private function fakePng(string $name): UploadedFile
    {
        $content = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j3ioAAAAASUVORK5CYII=', true);

        return UploadedFile::fake()->createWithContent($name, $content ?: '');
    }
}
