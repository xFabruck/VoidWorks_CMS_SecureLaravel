<?php

namespace App\Http\Requests\Admin;

use App\Models\Video;
use App\Support\VideoUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class VideoFormRequest extends FormRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'provider' => ['required', Rule::in(Video::PROVIDERS)],
            'video_url' => ['required', 'string', 'url:https', 'max:2048'],
            'thumbnail' => ['nullable', 'string', 'url:https', 'max:2048'],
            'position' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $provider = $this->input('provider');
            $videoUrl = $this->input('video_url');

            if (is_string($provider) && is_string($videoUrl) && VideoUrl::extractId($videoUrl, $provider) === null) {
                $validator->errors()->add('video_url', 'Usa una URL HTTPS válida de YouTube o Vimeo que coincida con el proveedor.');
            }

            $thumbnail = $this->input('thumbnail');
            if (is_string($provider) && is_string($thumbnail) && $thumbnail !== '' && ! VideoUrl::thumbnailAllowed($thumbnail, $provider)) {
                $validator->errors()->add('thumbnail', 'La miniatura debe provenir del CDN seguro del proveedor seleccionado.');
            }
        }];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'title' => 'título', 'description' => 'descripción', 'provider' => 'proveedor',
            'video_url' => 'URL del video', 'thumbnail' => 'miniatura', 'position' => 'orden', 'is_active' => 'estado',
        ];
    }
}
