<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TestimonialManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
    }

    public function test_guest_cannot_access_testimonial_administration(): void
    {
        $this->get(route('admin.testimonials.index'))->assertRedirect(route('login'));
        $this->get(route('admin.testimonials.create'))->assertRedirect(route('login'));
        $this->post(route('admin.testimonials.store'), [])->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_testimonial_with_generated_photo_name_and_optional_rating(): void
    {
        $upload = $this->fakePng('original-client-photo.png');
        $this->actingAs(User::factory()->create())
            ->post(route('admin.testimonials.store'), $this->formData($upload, active: true))
            ->assertRedirect(route('admin.testimonials.index'))
            ->assertSessionHas('status');

        $testimonial = Testimonial::query()->firstOrFail();
        $this->assertSame('Jordan Rivera', $testimonial->name);
        $this->assertSame('Producer, Northstar Games', $testimonial->position_or_company);
        $this->assertNull($testimonial->rating);
        $this->assertTrue($testimonial->is_active);
        $this->assertNotSame($upload->getClientOriginalName(), basename($testimonial->photo));
        Storage::disk('public')->assertExists($testimonial->photo);
    }

    public function test_rating_must_be_between_one_and_five(): void
    {
        $this->actingAs(User::factory()->create());
        foreach ([0, 6, 'not-a-rating'] as $rating) {
            $this->from(route('admin.testimonials.create'))
                ->post(route('admin.testimonials.store'), $this->formData($this->fakePng(), rating: $rating))
                ->assertRedirect(route('admin.testimonials.create'))
                ->assertSessionHasErrors('rating');
        }

        $this->assertDatabaseCount('testimonials', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_invalid_photo_and_missing_required_photo_are_rejected(): void
    {
        $this->actingAs(User::factory()->create());
        $this->from(route('admin.testimonials.create'))
            ->post(route('admin.testimonials.store'), $this->formData(UploadedFile::fake()->createWithContent('unsafe.svg', '<svg><script>alert(1)</script></svg>')))
            ->assertRedirect(route('admin.testimonials.create'))->assertSessionHasErrors('photo');

        $this->post(route('admin.testimonials.store'), $this->formData(null))
            ->assertSessionHasErrors('photo');

        $this->assertDatabaseCount('testimonials', 0);
    }

    public function test_public_page_shows_only_active_testimonials_in_order_and_escapes_text(): void
    {
        $this->testimonial_record('Later testimony', true, 2, 'Taylor Park · Northstar Games', 4);
        $this->testimonial_record('Hidden testimony', false, 0, 'Hidden company', 5);
        $this->testimonial_record('<script>alert(1)</script>', true, 1, 'Independent developer', 5, '<img src=x onerror=alert(1)>');

        $this->get(route('testimonials.index'))->assertOk()
            ->assertSeeInOrder(['<script>alert(1)</script>', 'Later testimony'])
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false)
            ->assertDontSee('Hidden testimony')
            ->assertDontSee('Hidden company');
    }

    public function test_admin_can_edit_reorder_toggle_and_delete_and_photos_are_cleaned_up(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.testimonials.store'), $this->formData($this->fakePng(), active: true))
            ->assertRedirect();
        $testimonial = Testimonial::query()->firstOrFail();
        $oldPhoto = $testimonial->photo;

        $this->put(route('admin.testimonials.update', $testimonial), $this->formData(active: true, rating: 3, position: 8))
            ->assertRedirect(route('admin.testimonials.index'));
        $testimonial->refresh();
        $this->assertSame(3, $testimonial->rating);
        $this->assertSame(8, $testimonial->position);
        $this->assertSame($oldPhoto, $testimonial->photo);

        $this->put(route('admin.testimonials.update', $testimonial), $this->formData($this->fakePng('replacement.png'), active: true, rating: 4))
            ->assertRedirect(route('admin.testimonials.index'));
        $testimonial->refresh();
        Storage::disk('public')->assertMissing($oldPhoto);
        Storage::disk('public')->assertExists($testimonial->photo);

        $this->patch(route('admin.testimonials.toggle', $testimonial))->assertRedirect();
        $this->assertDatabaseHas('testimonials', ['id' => $testimonial->id, 'is_active' => false]);
        $this->get(route('testimonials.index'))->assertDontSee('Jordan Rivera');

        $this->delete(route('admin.testimonials.destroy', $testimonial))->assertRedirect()->assertSessionHas('status');
        $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
        Storage::disk('public')->assertMissing($testimonial->photo);
    }

    private function formData(?UploadedFile $photo = null, bool $active = false, mixed $rating = null, int $position = 1): array
    {
        return [
            'name' => 'Jordan Rivera',
            'position_or_company' => 'Producer, Northstar Games',
            'testimonial' => 'The studio delivered a thoughtful and polished experience.',
            'photo' => $photo,
            'rating' => $rating,
            'position' => $position,
            'is_active' => $active ? '1' : '0',
        ];
    }

    private function testimonial_record(string $name, bool $active, int $position, string $positionOrCompany, ?int $rating, string $testimonial = 'A clear and helpful collaboration.'): Testimonial
    {
        return Testimonial::query()->create([
            'name' => $name,
            'position_or_company' => $positionOrCompany,
            'testimonial' => $testimonial,
            'photo' => 'testimonials/sample.png',
            'rating' => $rating,
            'position' => $position,
            'is_active' => $active,
        ]);
    }

    private function fakePng(string $name = 'testimonial.png'): UploadedFile
    {
        $content = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j3ioAAAAASUVORK5CYII=', true);

        return UploadedFile::fake()->createWithContent($name, $content ?: '');
    }
}
