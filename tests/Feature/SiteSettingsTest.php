<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SiteSettingsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
        Cache::flush();
    }

    public function test_guest_and_role_without_settings_permission_cannot_access_settings(): void
    {
        $this->get(route('admin.settings.edit'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['role' => 'editor']))
            ->get(route('admin.settings.edit'))->assertForbidden();
    }

    public function test_admin_can_update_public_settings_and_keeps_site_title_in_sync_with_seo(): void
    {
        $manager = app(SiteSettingsManager::class);
        $this->assertSame(config('seo.site_title'), $manager->current()->site_name);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.settings.edit'))->assertOk()->assertSee('Configuración general')->assertSee('Redes sociales');

        $this->put(route('admin.settings.update'), $this->settingsData([
            'site_name' => 'Estudio Aurora',
            'contact_email' => 'hola@example.test',
            'phone' => '+57 300 123 4567',
            'address' => 'Bogotá, Colombia',
            'description' => 'Estudio independiente.',
            'footer_text' => 'Todos los derechos reservados.',
            'locale' => 'es',
            'presentation_timezone' => 'America/Bogota',
        ]))->assertRedirect(route('admin.settings.edit'))->assertSessionHas('status');

        $this->assertDatabaseHas('site_settings', [
            'singleton_key' => 'main',
            'site_name' => 'Estudio Aurora',
            'contact_email' => 'hola@example.test',
            'presentation_timezone' => 'America/Bogota',
        ]);
        $this->assertDatabaseHas('seo_settings', ['singleton_key' => 'main', 'site_title' => 'Estudio Aurora']);
        $this->assertSame('Estudio Aurora', $manager->current()->site_name);

        $this->get(route('home'))->assertOk()
            ->assertSee('Estudio Aurora')
            ->assertSee('hola@example.test')
            ->assertSee('Todos los derechos reservados.')
            ->assertSee('<html lang="es">', false);
    }

    public function test_valid_brand_images_are_stored_safely_replaced_and_removed(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($user)->put(route('admin.settings.update'), $this->settingsData([
            'logo' => $this->fakePng('my-logo.png'),
            'favicon' => $this->fakePng('my-icon.png'),
        ]))->assertRedirect(route('admin.settings.edit'));

        $settings = SiteSetting::query()->firstOrFail();
        $oldLogo = $settings->logo_path;
        $oldFavicon = $settings->favicon_path;
        $this->assertStringStartsWith('site/branding/', $oldLogo);
        $this->assertStringStartsWith('site/branding/', $oldFavicon);
        $this->assertNotSame('my-logo.png', basename($oldLogo));
        Storage::disk('public')->assertExists($oldLogo);
        Storage::disk('public')->assertExists($oldFavicon);

        $this->put(route('admin.settings.update'), $this->settingsData([
            'logo' => $this->fakePng('replacement.png'),
        ]))->assertRedirect(route('admin.settings.edit'));

        $settings->refresh();
        $this->assertNotSame($oldLogo, $settings->logo_path);
        Storage::disk('public')->assertMissing($oldLogo);
        Storage::disk('public')->assertExists($settings->logo_path);
        Storage::disk('public')->assertExists($oldFavicon);

        $this->put(route('admin.settings.update'), $this->settingsData(['remove_logo' => '1', 'remove_favicon' => '1']))
            ->assertRedirect(route('admin.settings.edit'));
        $settings->refresh();
        $this->assertNull($settings->logo_path);
        $this->assertNull($settings->favicon_path);
        Storage::disk('public')->assertMissing($oldFavicon);
    }

    public function test_non_image_upload_and_unsupported_settings_are_rejected(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));

        $this->from(route('admin.settings.edit'))->put(route('admin.settings.update'), $this->settingsData([
            'logo' => UploadedFile::fake()->create('logo.php', 10, 'text/plain'),
        ]))->assertRedirect(route('admin.settings.edit'))->assertSessionHasErrors('logo');

        $this->from(route('admin.settings.edit'))->put(route('admin.settings.update'), $this->settingsData([
            'locale' => 'xx',
            'presentation_timezone' => 'Invalid/Timezone',
            'phone' => 'tel:javascript(1)',
        ]))->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHasErrors(['locale', 'presentation_timezone', 'phone']);

        $this->assertDatabaseCount('site_settings', 1);
        $this->assertNull(SiteSetting::query()->firstOrFail()->logo_path);
    }

    public function test_public_text_is_escaped_and_infrastructure_values_are_ignored(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->put(route('admin.settings.update'), $this->settingsData([
                'site_name' => '<script>alert(1)</script>',
                'description' => '<img src=x onerror=alert(1)>',
                'footer_text' => '<script>steal()</script>',
                'APP_KEY' => 'forbidden-test-value',
                'DB_PASSWORD' => 'forbidden-db-password',
            ]))->assertRedirect(route('admin.settings.edit'));

        $this->assertDatabaseMissing('site_settings', ['site_name' => 'forbidden-test-value']);
        $this->assertDatabaseMissing('site_settings', ['site_name' => 'forbidden-db-password']);
        $this->get(route('home'))->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    /** @return array<string, mixed> */
    private function settingsData(array $overrides = []): array
    {
        return array_replace([
            'site_name' => 'Voidworks Studio',
            'remove_logo' => '0',
            'remove_favicon' => '0',
            'contact_email' => '',
            'phone' => '',
            'address' => '',
            'description' => '',
            'footer_text' => '',
            'locale' => 'es',
            'presentation_timezone' => 'America/Bogota',
        ], $overrides);
    }

    private function fakePng(string $name): UploadedFile
    {
        $content = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j3ioAAAAASUVORK5CYII=', true);

        return UploadedFile::fake()->createWithContent($name, $content ?: '');
    }
}
