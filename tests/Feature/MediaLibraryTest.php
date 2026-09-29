<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
    }

    public function test_guest_cannot_access_media_library_upload_or_file(): void
    {
        $media = $this->imageRecord(User::factory()->create());

        $this->get(route('admin.media.index'))->assertRedirect(route('login'));
        $this->post(route('admin.media.store'), [])->assertRedirect(route('login'));
        $this->get(route('admin.media.file', $media))->assertRedirect(route('login'));
        $this->delete(route('admin.media.destroy', $media))->assertRedirect(route('login'));
    }

    public function test_user_can_upload_image_with_real_mime_and_generated_private_name(): void
    {
        $user = User::factory()->create();
        $upload = $this->fakePng('original name.png');

        $this->actingAs($user)->post(route('admin.media.store'), [
            'file' => $upload,
            'alt_text' => 'Portada del proyecto',
            'caption' => 'Imagen de prueba',
        ])->assertRedirect(route('admin.media.index'))->assertSessionHas('status');

        $media = Media::query()->firstOrFail();
        $this->assertSame('image/png', $media->mime_type);
        $this->assertSame('png', $media->extension);
        $this->assertSame('image', $media->type);
        $this->assertSame($user->id, $media->uploaded_by);
        $this->assertSame('Portada del proyecto', $media->alt_text);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}\.png$/', $media->file_name);
        $this->assertSame('media/'.$media->file_name, $media->path);
        $this->assertNotSame($upload->getClientOriginalName(), $media->file_name);
        Storage::disk('local')->assertExists($media->path);

        $this->get(route('admin.media.file', $media))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Content-Security-Policy', "default-src 'none'; sandbox");
    }

    public function test_user_can_upload_pdf_as_document_and_it_is_not_rendered_inline(): void
    {
        $user = User::factory()->create();
        $pdf = UploadedFile::fake()->createWithContent('manual.pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF");

        $this->actingAs($user)->post(route('admin.media.store'), ['file' => $pdf])->assertRedirect();
        $media = Media::query()->firstOrFail();

        $this->assertSame('application/pdf', $media->mime_type);
        $this->assertSame('document', $media->type);
        $this->get(route('admin.media.file', $media))->assertOk()->assertHeader('Content-Disposition', 'attachment; filename="'.$media->file_name.'"');
    }

    public function test_malicious_extensions_and_content_extension_mismatch_are_rejected(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (['php', 'phtml', 'phar', 'exe', 'sh', 'bat', 'cmd', 'js', 'html'] as $extension) {
            $response = $this->post(route('admin.media.store'), [
                'file' => UploadedFile::fake()->createWithContent('payload.'.$extension, '<?php echo "unsafe"; ?>'),
            ]);
            $response->assertSessionHasErrors('file');
        }

        $renamedPayload = UploadedFile::fake()->createWithContent('script.jpg', '<?php echo "unsafe"; ?>');
        $this->post(route('admin.media.store'), ['file' => $renamedPayload])->assertSessionHasErrors('file');

        $this->assertDatabaseCount('media', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_upload_size_limit_is_enforced(): void
    {
        $oversized = UploadedFile::fake()->create('large.pdf', 10241, 'application/pdf');

        $this->actingAs(User::factory()->create())
            ->post(route('admin.media.store'), ['file' => $oversized])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('media', 0);
    }

    public function test_only_uploader_can_delete_media_and_file_is_removed(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $media = $this->imageRecord($owner);
        Storage::disk('local')->put($media->path, 'fake image');

        $this->actingAs($other)->delete(route('admin.media.destroy', $media))->assertForbidden();
        $this->assertDatabaseHas('media', ['id' => $media->id]);

        $this->actingAs($owner)->delete(route('admin.media.destroy', $media))->assertRedirect(route('admin.media.index'))->assertSessionHas('status');
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        Storage::disk('local')->assertMissing($media->path);
    }

    public function test_library_lists_uploader_and_escapes_metadata(): void
    {
        $owner = User::factory()->create(['name' => 'Studio Owner']);
        $media = $this->imageRecord($owner);
        $media->update(['name' => '<script>alert(1)</script>', 'caption' => '<b>caption</b>']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee('Studio Owner')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    private function imageRecord(User $user): Media
    {
        return Media::query()->create([
            'name' => 'sample',
            'file_name' => '00000000-0000-4000-8000-000000000000.png',
            'path' => 'media/00000000-0000-4000-8000-000000000000.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'extension' => 'png',
            'size' => 42,
            'type' => 'image',
            'uploaded_by' => $user->id,
        ]);
    }

    private function fakePng(string $name): UploadedFile
    {
        $chunk = static fn (string $type, string $data): string => pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
        $png = "\x89PNG\r\n\x1a\n";
        $png .= $chunk('IHDR', pack('N2C5', 1, 1, 8, 2, 0, 0, 0));
        $png .= $chunk('IDAT', gzcompress("\x00\x00\xff\x00"));
        $png .= $chunk('IEND', '');

        return UploadedFile::fake()->createWithContent($name, $png);
    }
}
