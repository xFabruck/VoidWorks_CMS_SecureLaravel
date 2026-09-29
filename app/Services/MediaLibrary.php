<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class MediaLibrary
{
    /** @param array{alt_text?: string|null, caption?: string|null} $data */
    public function upload(UploadedFile $file, array $data, int $userId): Media
    {
        $mime = strtolower((string) $file->getMimeType());
        $types = [
            'image/jpeg' => ['jpg', 'image'],
            'image/png' => ['png', 'image'],
            'image/webp' => ['webp', 'image'],
            'application/pdf' => ['pdf', 'document'],
        ];

        if (! isset($types[$mime])) {
            throw new RuntimeException('El tipo real del archivo no está permitido.');
        }

        [$extension, $type] = $types[$mime];
        $fileName = Str::uuid()->toString().'.'.$extension;
        $path = $file->storeAs('media', $fileName, 'local');

        if (! is_string($path)) {
            throw new RuntimeException('No se pudo almacenar el archivo.');
        }

        $originalName = pathinfo(str_replace('\\', '/', $file->getClientOriginalName()), PATHINFO_FILENAME);
        $displayName = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $originalName) ?? '');
        $displayName = Str::limit($displayName !== '' ? $displayName : 'Archivo', 255, '');

        try {
            return DB::transaction(fn (): Media => Media::query()->create([
                'name' => $displayName,
                'file_name' => $fileName,
                'path' => $path,
                'disk' => 'local',
                'mime_type' => $mime,
                'extension' => $extension,
                'size' => (int) $file->getSize(),
                'type' => $type,
                'alt_text' => $data['alt_text'] ?? null,
                'caption' => $data['caption'] ?? null,
                'uploaded_by' => $userId,
            ]));
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }
    }

    public function delete(Media $media): void
    {
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();
    }
}
