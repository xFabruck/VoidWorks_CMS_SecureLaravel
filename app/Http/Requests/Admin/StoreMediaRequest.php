<?php

namespace App\Http\Requests\Admin;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Media::class) ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $file = $this->file('file');
        $rules = [
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf', 'extensions:jpg,jpeg,png,webp,pdf'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:2000'],
        ];

        if ($file && str_starts_with((string) $file->getMimeType(), 'image/')) {
            $rules['file'][] = 'image';
            $rules['file'][] = 'dimensions:max_width=6000,max_height=6000';
        }

        return $rules;
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $file = $this->file('file');

            if (! $file || ! $file->isValid()) {
                return;
            }

            $mimeMap = [
                'image/jpeg' => ['jpg', 'jpeg', 'image'],
                'image/png' => ['png', 'image'],
                'image/webp' => ['webp', 'image'],
                'application/pdf' => ['pdf', 'document'],
            ];
            $mime = strtolower((string) $file->getMimeType());
            $extension = strtolower($file->getClientOriginalExtension());

            if (! isset($mimeMap[$mime]) || ! in_array($extension, $mimeMap[$mime], true)) {
                $validator->errors()->add('file', 'El contenido real del archivo no coincide con un formato permitido.');
            }
        }];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['file' => 'archivo', 'alt_text' => 'texto alternativo', 'caption' => 'descripción'];
    }
}
