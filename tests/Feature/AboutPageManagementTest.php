<?php

namespace Tests\Feature;

use App\Models\AboutPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AboutPageManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_guest_cannot_open_institutional_admin(): void
    {
        $this->get(route('admin.about.edit'))->assertRedirect(route('login'));
        $this->put(route('admin.about.update'), $this->formData())->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_manage_the_single_institutional_record(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('admin.about.edit'))
            ->assertOk()->assertSee('Nosotros / Institucional')->assertSee('Imagen principal');

        $this->put(route('admin.about.update'), $this->formData())
            ->assertRedirect(route('admin.about.edit'))->assertSessionHas('status');

        $this->assertDatabaseCount('about_pages', 1);
        $this->assertDatabaseHas('about_pages', ['singleton_key' => 'main', 'title' => 'Studio story']);

        $this->put(route('admin.about.update'), [...$this->formData(), 'title' => 'Updated studio story'])
            ->assertRedirect(route('admin.about.edit'));
        $this->assertDatabaseCount('about_pages', 1);
        $this->assertDatabaseHas('about_pages', ['singleton_key' => 'main', 'title' => 'Updated studio story']);
    }

    public function test_about_policy_only_allows_the_singleton_record(): void
    {
        $user = User::factory()->create();
        $main = new AboutPage;
        $main->setAttribute('singleton_key', 'main');
        $other = new AboutPage;
        $other->setAttribute('singleton_key', 'other');

        $this->assertTrue(Gate::forUser($user)->allows('update', $main));
        $this->assertFalse(Gate::forUser($user)->allows('update', $other));
    }

    public function test_valid_images_are_stored_with_generated_names(): void
    {
        $user = User::factory()->create();
        $primary = $this->fakePng('primary-from-user.png');
        $secondary = $this->fakePng('secondary-from-user.png');

        $this->actingAs($user)->put(route('admin.about.update'), $this->formData($primary, $secondary))
            ->assertRedirect(route('admin.about.edit'));

        $aboutPage = AboutPage::query()->firstOrFail();
        $this->assertNotSame('primary-from-user.png', basename((string) $aboutPage->primary_image));
        $this->assertNotSame('secondary-from-user.png', basename((string) $aboutPage->secondary_image));
        Storage::disk('public')->assertExists((string) $aboutPage->primary_image);
        Storage::disk('public')->assertExists((string) $aboutPage->secondary_image);
    }

    public function test_invalid_image_types_are_rejected(): void
    {
        $invalid = UploadedFile::fake()->createWithContent('unsafe.svg', '<svg><script>alert(1)</script></svg>');

        $this->actingAs(User::factory()->create())
            ->from(route('admin.about.edit'))
            ->put(route('admin.about.update'), $this->formData($invalid, $invalid))
            ->assertRedirect(route('admin.about.edit'))
            ->assertSessionHasErrors(['primary_image', 'secondary_image']);

        $this->assertDatabaseCount('about_pages', 0);
    }

    public function test_image_larger_than_five_megabytes_is_rejected(): void
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j3ioAAAAASUVORK5CYII=', true);
        $oversized = UploadedFile::fake()->createWithContent('oversized.png', ($png ?: '').str_repeat('0', 5 * 1024 * 1024));

        $this->actingAs(User::factory()->create())
            ->from(route('admin.about.edit'))
            ->put(route('admin.about.update'), $this->formData($oversized))
            ->assertRedirect(route('admin.about.edit'))
            ->assertSessionHasErrors('primary_image');

        $this->assertDatabaseCount('about_pages', 0);
    }

    public function test_replacing_each_image_removes_the_previous_file(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put(route('admin.about.update'), $this->formData($this->fakePng(), $this->fakePng()))->assertRedirect();
        $aboutPage = AboutPage::query()->firstOrFail();
        $oldPrimary = $aboutPage->primary_image;
        $oldSecondary = $aboutPage->secondary_image;

        $this->put(route('admin.about.update'), $this->formData($this->fakePng('new-primary.png'), $this->fakePng('new-secondary.png')))->assertRedirect();
        $aboutPage->refresh();

        Storage::disk('public')->assertMissing((string) $oldPrimary);
        Storage::disk('public')->assertMissing((string) $oldSecondary);
        Storage::disk('public')->assertExists((string) $aboutPage->primary_image);
        Storage::disk('public')->assertExists((string) $aboutPage->secondary_image);
    }

    public function test_public_page_requires_active_database_content_and_escapes_it(): void
    {
        $this->get(route('about'))->assertNotFound();

        $this->actingAs(User::factory()->create())->put(route('admin.about.update'), $this->formData(isActive: false));
        $this->get(route('about'))->assertNotFound();

        $this->put(route('admin.about.update'), [...$this->formData(isActive: true), 'title' => '<script>alert(1)</script>']);
        $response = $this->get(route('about'))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertSee('Misión desde base de datos')
            ->assertSee('Historia desde base de datos')
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    /** @return array<string, mixed> */
    private function formData(?UploadedFile $primary = null, ?UploadedFile $secondary = null, bool $isActive = true): array
    {
        return [
            'title' => 'Studio story',
            'subtitle' => 'Institutional subtitle',
            'content' => 'Main institutional content.',
            'primary_image' => $primary,
            'mission' => 'Misión desde base de datos',
            'vision' => 'Visión desde base de datos',
            'values' => 'Valores desde base de datos',
            'history' => 'Historia desde base de datos',
            'secondary_image' => $secondary,
            'is_active' => $isActive ? '1' : '0',
        ];
    }

    private function fakePng(string $name = 'institution.png'): UploadedFile
    {
        $content = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j3ioAAAAASUVORK5CYII=', true);

        return UploadedFile::fake()->createWithContent($name, $content ?: '');
    }
}
