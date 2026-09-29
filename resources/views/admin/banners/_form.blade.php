@php($editing = $banner->exists)

<form method="POST" action="{{ $editing ? route('admin.banners.update', $banner) : route('admin.banners.store') }}" enctype="multipart/form-data" class="mt-7 space-y-6">
    @csrf
    @if ($editing) @method('PUT') @endif

    @if ($errors->any())
        <div role="alert" class="rounded-sm border border-red-400/30 bg-red-400/10 p-4 text-sm text-red-200">
            <p class="font-medium">Revisa los campos marcados:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="title" class="mono-label mb-2 block text-white/65">Título *</label>
            <input id="title" name="title" value="{{ old('title', $banner->title) }}" required maxlength="160" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15" aria-describedby="title-error">
            @error('title')<p id="title-error" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
        </div>
        <div class="sm:col-span-2">
            <label for="subtitle" class="mono-label mb-2 block text-white/65">Subtítulo</label>
            <input id="subtitle" name="subtitle" value="{{ old('subtitle', $banner->subtitle) }}" maxlength="220" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15" aria-describedby="subtitle-error">
            @error('subtitle')<p id="subtitle-error" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="image" class="mono-label mb-2 block text-white/65">Imagen {{ $editing ? '(opcional para conservar la actual)' : '*' }}</label>
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" @required(! $editing) class="block w-full rounded-sm border border-white/10 bg-black/35 p-3 text-sm text-white file:mr-4 file:rounded-sm file:border-0 file:bg-cyan file:px-3 file:py-2 file:font-mono file:text-xs file:font-semibold file:text-void" aria-describedby="image-help image-error">
            <p id="image-help" class="mt-2 text-xs text-white/45">Solo JPG, JPEG, PNG o WebP. Máximo 5 MB y 6000 × 6000 px. SVG no permitido.</p>
            @error('image')<p id="image-error" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
            @if ($editing)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($banner->image) }}" alt="{{ $banner->image_alt }}" class="mt-4 h-28 w-48 rounded-sm border border-white/10 object-cover">
            @endif
        </div>
        <div>
            <label for="image_alt" class="mono-label mb-2 block text-white/65">Texto alternativo de la imagen *</label>
            <input id="image_alt" name="image_alt" value="{{ old('image_alt', $banner->image_alt) }}" required maxlength="255" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15" aria-describedby="image_alt-error">
            @error('image_alt')<p id="image_alt-error" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="button_text" class="mono-label mb-2 block text-white/65">Texto del botón</label>
            <input id="button_text" name="button_text" value="{{ old('button_text', $banner->button_text) }}" maxlength="100" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15" aria-describedby="button_text-error">
            @error('button_text')<p id="button_text-error" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="button_url" class="mono-label mb-2 block text-white/65">URL del botón</label>
            <input id="button_url" name="button_url" type="url" value="{{ old('button_url', $banner->button_url) }}" maxlength="2048" placeholder="https://…" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15" aria-describedby="button_url-error">
            @error('button_url')<p id="button_url-error" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="position" class="mono-label mb-2 block text-white/65">Orden</label>
            <input id="position" name="position" type="number" min="0" max="65535" step="1" required value="{{ old('position', $banner->position ?? 0) }}" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15" aria-describedby="position-error">
            @error('position')<p id="position-error" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-center gap-3 self-center">
            <input type="hidden" name="is_active" value="0">
            <input id="is_active" name="is_active" type="checkbox" value="1" @checked((bool) old('is_active', $banner->is_active ?? false)) class="h-4 w-4 accent-cyan">
            <label for="is_active" class="text-sm text-white/75">Activo</label>
        </div>
        <div>
            <label for="starts_at" class="mono-label mb-2 block text-white/65">Inicio</label>
            <input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', $banner->starts_at?->format('Y-m-d\TH:i') ?? '') }}" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15" aria-describedby="starts_at-error">
            @error('starts_at')<p id="starts_at-error" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="ends_at" class="mono-label mb-2 block text-white/65">Fin</label>
            <input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', $banner->ends_at?->format('Y-m-d\TH:i') ?? '') }}" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15" aria-describedby="ends_at-error">
            @error('ends_at')<p id="ends_at-error" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex flex-wrap gap-3 border-t border-white/10 pt-5">
        <button type="submit" class="focus-cyan rounded-sm bg-cyan px-5 py-3 font-mono text-xs font-semibold tracking-wider text-void transition hover:brightness-110">{{ $editing ? 'GUARDAR CAMBIOS' : 'CREAR BANNER' }}</button>
        <a href="{{ route('admin.banners.index') }}" class="focus-cyan rounded-sm border border-white/15 px-5 py-3 font-mono text-xs tracking-wider text-white/70 hover:border-white/30">CANCELAR</a>
    </div>
</form>
