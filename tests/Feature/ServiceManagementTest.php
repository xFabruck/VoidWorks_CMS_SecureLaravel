<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_guest_cannot_open_service_management(): void
    {
        $this->get(route('admin.services.index'))->assertRedirect(route('login'));
        $this->get(route('admin.services.create'))->assertRedirect(route('login'));
    }

    public function test_user_cannot_update_or_delete_another_users_service(): void
    {
        $service = Service::query()->create($this->attributes(User::factory()->create()));
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)->get(route('admin.services.edit', $service))->assertForbidden();
        $this->put(route('admin.services.update', $service), $this->formData())->assertForbidden();
        $this->patch(route('admin.services.toggle', $service))->assertForbidden();
        $this->delete(route('admin.services.destroy', $service))->assertForbidden();
    }

    public function test_owner_can_open_service_list_and_form(): void
    {
        $user = User::factory()->create();
        Service::query()->create($this->attributes($user));

        $this->actingAs($user)->get(route('admin.services.index'))
            ->assertOk()->assertSee('Test service')->assertSee('NUEVO SERVICIO');
        $this->get(route('admin.services.create'))->assertOk()->assertSee('Crear servicio')->assertSee('multipart/form-data', false);
    }

    public function test_service_is_created_with_unique_automatic_slug_and_creator(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('admin.services.store'), $this->formData())
            ->assertRedirect(route('admin.services.index'))->assertSessionHas('status');
        $first = Service::query()->firstOrFail();

        $this->assertSame('test-service', $first->slug);
        $this->assertSame($user->id, $first->created_by);

        $this->post(route('admin.services.store'), $this->formData())
            ->assertRedirect(route('admin.services.index'));
        $this->assertDatabaseHas('services', ['slug' => 'test-service-2']);
    }

    public function test_slug_can_be_customized_and_must_be_unique(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('admin.services.store'), [...$this->formData(), 'slug' => 'servicio-a-medida'])
            ->assertRedirect(route('admin.services.index'));
        $service = Service::query()->firstOrFail();

        $this->assertSame('servicio-a-medida', $service->slug);
        $this->from(route('admin.services.create'))
            ->post(route('admin.services.store'), [...$this->formData(), 'slug' => 'servicio-a-medida'])
            ->assertRedirect(route('admin.services.create'))
            ->assertSessionHasErrors('slug');
    }

    public function test_valid_image_uses_generated_name_and_is_stored(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('admin.services.store'), $this->formData($this->fakePng('user-file.png')))
            ->assertRedirect(route('admin.services.index'));
        $service = Service::query()->firstOrFail();

        $this->assertNotSame('user-file.png', basename((string) $service->image));
        Storage::disk('public')->assertExists((string) $service->image);
    }

    public function test_invalid_file_type_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('admin.services.create'))
            ->post(route('admin.services.store'), $this->formData(UploadedFile::fake()->createWithContent('unsafe.svg', '<svg><script>alert(1)</script></svg>')))
            ->assertRedirect(route('admin.services.create'))
            ->assertSessionHasErrors('image');

        $this->assertDatabaseCount('services', 0);
    }

    public function test_owner_can_edit_reorder_toggle_and_delete_service(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('admin.services.store'), $this->formData($this->fakePng()))
            ->assertRedirect(route('admin.services.index'));
        $service = Service::query()->firstOrFail();

        $this->put(route('admin.services.update', $service), [...$this->formData(), 'name' => 'Updated service', 'slug' => 'updated-service', 'position' => 8])
            ->assertRedirect(route('admin.services.index'));
        $this->assertDatabaseHas('services', ['id' => $service->id, 'slug' => 'updated-service', 'position' => 8]);

        $this->patch(route('admin.services.toggle', $service))->assertRedirect(route('admin.services.index'));
        $this->assertDatabaseHas('services', ['id' => $service->id, 'is_active' => false]);

        Storage::disk('public')->put($service->image, 'fake image');
        $this->delete(route('admin.services.destroy', $service))->assertRedirect(route('admin.services.index'));
        $this->assertDatabaseMissing('services', ['id' => $service->id]);
        Storage::disk('public')->assertMissing($service->image);
    }

    public function test_replacing_image_deletes_previous_file(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('admin.services.store'), $this->formData($this->fakePng()))->assertRedirect();
        $service = Service::query()->firstOrFail();
        $oldImage = $service->image;

        $this->put(route('admin.services.update', $service), $this->formData($this->fakePng('replacement.png')))->assertRedirect();
        $service->refresh();

        Storage::disk('public')->assertMissing((string) $oldImage);
        Storage::disk('public')->assertExists((string) $service->image);
    }

    public function test_public_listing_contains_only_active_services_in_position_order(): void
    {
        $user = User::factory()->create();
        Service::query()->create($this->attributes($user, 'Second item', active: true, position: 2));
        Service::query()->create($this->attributes($user, 'First item', active: true, position: 1));
        Service::query()->create($this->attributes($user, 'Hidden item', active: false, position: 0));

        $this->get(route('services.index'))->assertOk()->assertSeeInOrder(['First item', 'Second item'])
            ->assertDontSee('Hidden item');
    }

    public function test_only_active_service_detail_is_public_and_description_is_escaped(): void
    {
        $user = User::factory()->create();
        $active = Service::query()->create($this->attributes($user, 'Public service', active: true, description: '<script>alert(1)</script>'));
        $inactive = Service::query()->create($this->attributes($user, 'Private service', active: false));

        $this->get(route('services.show', $active->slug))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
        $this->get(route('services.show', $inactive->slug))->assertNotFound();
        $this->get(route('services.show', 'missing-service'))->assertNotFound();
    }

    /** @return array<string, mixed> */
    private function formData(?UploadedFile $image = null): array
    {
        return [
            'name' => 'Test service',
            'short_description' => 'Short service description',
            'description' => 'A full plain-text service description.',
            'icon' => 'gamepad-2',
            'image' => $image,
            'button_text' => 'Learn more',
            'button_url' => 'https://example.test/services',
            'position' => 1,
            'is_active' => '1',
            'seo_title' => 'Test service SEO',
            'meta_description' => 'Service description for search engines.',
        ];
    }

    /** @return array<string, mixed> */
    private function attributes(User $creator, string $name = 'Test service', bool $active = true, int $position = 1, string $description = 'Safe description'): array
    {
        return [
            'name' => $name,
            'slug' => str($name)->slug(),
            'short_description' => 'Short description',
            'description' => $description,
            'icon' => 'gamepad-2',
            'position' => $position,
            'is_active' => $active,
            'created_by' => $creator->id,
        ];
    }

    private function fakePng(string $name = 'service.png'): UploadedFile
    {
        $content = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j3ioAAAAASUVORK5CYII=', true);

        return UploadedFile::fake()->createWithContent($name, $content ?: '');
    }
}
