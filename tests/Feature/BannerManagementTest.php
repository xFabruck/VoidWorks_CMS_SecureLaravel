<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_guest_cannot_open_banner_management(): void
    {
        $this->get(route('admin.banners.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_banner_permission_cannot_manage_another_users_banner(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        $banner = Banner::query()->create($this->attributes($creator));

        $this->actingAs($otherUser)
            ->get(route('admin.banners.edit', $banner))->assertForbidden();
        $this->get(route('admin.banners.preview', $banner))->assertForbidden();
        $this->patch(route('admin.banners.toggle', $banner))->assertForbidden();
        $this->delete(route('admin.banners.destroy', $banner))->assertForbidden();

        $this->assertDatabaseHas('banners', ['id' => $banner->id, 'is_active' => true]);
    }

    public function test_authenticated_user_can_open_banner_list_and_form(): void
    {
        $user = User::factory()->create();
        Banner::query()->create($this->attributes($user));

        $this->actingAs($user)
            ->get(route('admin.banners.index'))
            ->assertOk()
            ->assertSee('Test hero')
            ->assertSee('NUEVO BANNER')
            ->assertSee('Inicio')
            ->assertSee('Fin');

        $this->get(route('admin.banners.create'))
            ->assertOk()
            ->assertSee('Crear banner')
            ->assertSee('multipart/form-data', false);
    }

    public function test_valid_image_is_stored_with_generated_name_and_creator(): void
    {
        $user = User::factory()->create();
        $upload = $this->fakePng('cliente-nombre.png');

        $this->actingAs($user)
            ->post(route('admin.banners.store'), $this->formData($upload))
            ->assertRedirect(route('admin.banners.index'))
            ->assertSessionHas('status');

        $banner = Banner::query()->firstOrFail();

        $this->assertSame($user->id, $banner->created_by);
        $this->assertNotSame('cliente-nombre.png', basename($banner->image));
        Storage::disk('public')->assertExists($banner->image);
    }

    public function test_invalid_file_type_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('admin.banners.create'))
            ->post(route('admin.banners.store'), $this->formData(
                UploadedFile::fake()->createWithContent('unsafe.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            ))
            ->assertRedirect(route('admin.banners.create'))
            ->assertSessionHasErrors('image');

        $this->assertDatabaseCount('banners', 0);
    }

    public function test_image_larger_than_five_megabytes_is_rejected(): void
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j3ioAAAAASUVORK5CYII=', true);
        $largeImage = UploadedFile::fake()->createWithContent('large.png', ($png ?: '').str_repeat('0', 5 * 1024 * 1024));

        $this->actingAs(User::factory()->create())
            ->from(route('admin.banners.create'))
            ->post(route('admin.banners.store'), $this->formData($largeImage))
            ->assertRedirect(route('admin.banners.create'))
            ->assertSessionHasErrors('image');

        $this->assertDatabaseCount('banners', 0);
    }

    public function test_replacing_an_image_deletes_the_previous_file(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('admin.banners.store'), $this->formData($this->fakePng()));
        $banner = Banner::query()->firstOrFail();
        $oldImage = $banner->image;

        $this->put(route('admin.banners.update', $banner), $this->formData($this->fakePng('replacement.png')))
            ->assertRedirect(route('admin.banners.index'));

        $banner->refresh();
        $this->assertNotSame($oldImage, $banner->image);
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($banner->image);
    }

    public function test_creator_can_toggle_banner_visibility(): void
    {
        $creator = User::factory()->create();
        $banner = Banner::query()->create($this->attributes($creator));

        $this->actingAs($creator)
            ->patch(route('admin.banners.toggle', $banner))
            ->assertRedirect(route('admin.banners.index'));

        $this->assertDatabaseHas('banners', ['id' => $banner->id, 'is_active' => false]);

        $this->patch(route('admin.banners.toggle', $banner))
            ->assertRedirect(route('admin.banners.index'));

        $this->assertDatabaseHas('banners', ['id' => $banner->id, 'is_active' => true]);
    }

    public function test_creator_can_delete_banner_and_its_image(): void
    {
        $creator = User::factory()->create();
        $banner = Banner::query()->create($this->attributes($creator));
        Storage::disk('public')->put($banner->image, 'fake image');

        $this->actingAs($creator)
            ->delete(route('admin.banners.destroy', $banner))
            ->assertRedirect(route('admin.banners.index'));

        $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
        Storage::disk('public')->assertMissing($banner->image);
    }

    public function test_preview_is_available_to_the_banner_creator(): void
    {
        $creator = User::factory()->create();
        $banner = Banner::query()->create($this->attributes($creator, isActive: false));

        $this->actingAs($creator)
            ->get(route('admin.banners.preview', $banner))
            ->assertOk()
            ->assertSee('VISTA PREVIA PRIVADA')
            ->assertSee('Test hero');
    }

    public function test_homepage_excludes_inactive_future_and_expired_banners(): void
    {
        $creator = User::factory()->create();
        Banner::query()->create($this->attributes($creator, title: 'Inactive hero', isActive: false));
        Banner::query()->create($this->attributes($creator, title: 'Future hero', startsAt: now()->addDay()));
        Banner::query()->create($this->attributes($creator, title: 'Expired hero', endsAt: now()->subDay()));

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Inactive hero')
            ->assertDontSee('Future hero')
            ->assertDontSee('Expired hero');
    }

    public function test_homepage_displays_an_active_banner(): void
    {
        $creator = User::factory()->create();
        Banner::query()->create($this->attributes($creator, title: 'Current hero'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Current hero')
            ->assertSee('data-hero-slide', false)
            ->assertDontSee('data-hero-carousel', false)
            ->assertSee('object-cover', false);
    }

    public function test_two_visible_banners_render_carousel_controls_in_position_order(): void
    {
        $creator = User::factory()->create();
        Banner::query()->create($this->attributes($creator, title: 'Second position', position: 2));
        Banner::query()->create($this->attributes($creator, title: 'First position', position: 1));

        $response = $this->get(route('home'))->assertOk()
            ->assertSee('data-hero-carousel', false)
            ->assertSee('data-carousel-next', false)
            ->assertSee('Mostrar banner 2');

        $response->assertSeeInOrder(['First position', 'Second position']);
    }

    public function test_end_date_must_be_after_start_date(): void
    {
        $startsAt = now()->addDays(2)->format('Y-m-d\TH:i');
        $endsAt = now()->addDay()->format('Y-m-d\TH:i');

        $this->actingAs(User::factory()->create())
            ->from(route('admin.banners.create'))
            ->post(route('admin.banners.store'), [
                ...$this->formData($this->fakePng()),
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ])
            ->assertRedirect(route('admin.banners.create'))
            ->assertSessionHasErrors('ends_at');
    }

    /** @return array<string, mixed> */
    private function formData(UploadedFile $image): array
    {
        return [
            'title' => 'Test hero',
            'subtitle' => 'Subtítulo de prueba',
            'image' => $image,
            'image_alt' => 'Paisaje espacial de prueba',
            'button_text' => 'Ver proyecto',
            'button_url' => 'https://example.test/project',
            'position' => 4,
            'starts_at' => null,
            'ends_at' => null,
            'is_active' => '1',
        ];
    }

    /** @return array<string, mixed> */
    private function attributes(
        User $creator,
        string $title = 'Test hero',
        int $position = 0,
        bool $isActive = true,
        mixed $startsAt = null,
        mixed $endsAt = null,
    ): array {
        return [
            'title' => $title,
            'subtitle' => 'Subtítulo de prueba',
            'image' => 'banners/'.str($title)->slug().'.png',
            'image_alt' => 'Imagen de prueba',
            'button_text' => 'Ver proyecto',
            'button_url' => 'https://example.test/project',
            'position' => $position,
            'is_active' => $isActive,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'created_by' => $creator->id,
        ];
    }

    private function fakePng(string $name = 'hero.png'): UploadedFile
    {
        $content = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j3ioAAAAASUVORK5CYII=', true);

        return UploadedFile::fake()->createWithContent($name, $content ?: '');
    }
}
