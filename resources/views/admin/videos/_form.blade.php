@php($editing = $video->exists)

<form method="POST" action="{{ $editing ? route('admin.videos.update', $video) : route('admin.videos.store') }}" class="mt-7 space-y-6">
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
            <input id="title" name="title" value="{{ old('title', $video->title) }}" required maxlength="180" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">
            @error('title')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
        </div>
        <div class="sm:col-span-2">
            <label for="description" class="mono-label mb-2 block text-white/65">Descripción</label>
            <textarea id="description" name="description" rows="4" maxlength="5000" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm leading-6 text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">{{ old('description', $video->description) }}</textarea>
            @error('description')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="provider" class="mono-label mb-2 block text-white/65">Proveedor *</label>
            <select id="provider" name="provider" required class="w-full rounded-sm border border-white/10 bg-[#111217] px-4 py-3 text-sm text-white outline-none focus:border-cyan">
                <option value="youtube" @selected(old('provider', $video->provider ?? 'youtube') === 'youtube')>YouTube</option>
                <option value="vimeo" @selected(old('provider', $video->provider) === 'vimeo')>Vimeo</option>
            </select>
            @error('provider')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="video_url" class="mono-label mb-2 block text-white/65">URL del video *</label>
            <input id="video_url" name="video_url" type="url" inputmode="url" value="{{ old('video_url', $video->video_url) }}" required maxlength="2048" placeholder="https://www.youtube.com/watch?v=…" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">
            <p class="mt-2 text-xs text-white/40">Se aceptan enlaces HTTPS oficiales de YouTube o Vimeo. No pegues código iframe.</p>
            @error('video_url')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
        </div>
        <div class="sm:col-span-2">
            <label for="thumbnail" class="mono-label mb-2 block text-white/65">URL de miniatura · opcional</label>
            <input id="thumbnail" name="thumbnail" type="url" inputmode="url" value="{{ old('thumbnail', $video->thumbnail) }}" maxlength="2048" placeholder="HTTPS desde el CDN del proveedor" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">
            <p class="mt-2 text-xs text-white/40">YouTube genera una miniatura automáticamente. Si indicas una, su dominio debe ser el CDN oficial de YouTube o Vimeo.</p>
            @error('thumbnail')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
            @if ($editing && $video->thumbnail)<img src="{{ $video->thumbnail }}" alt="Miniatura de {{ $video->title }}" loading="lazy" referrerpolicy="no-referrer" class="mt-3 aspect-video w-64 rounded-sm border border-white/10 object-cover">@endif
        </div>
        <div>
            <label for="position" class="mono-label mb-2 block text-white/65">Orden</label>
            <input id="position" name="position" type="number" min="0" max="65535" step="1" required value="{{ old('position', $video->position ?? 0) }}" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">
            @error('position')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-center gap-3 self-center">
            <input type="hidden" name="is_active" value="0">
            <input id="is_active" name="is_active" type="checkbox" value="1" @checked((bool) old('is_active', $video->is_active ?? false)) class="h-4 w-4 accent-cyan">
            <label for="is_active" class="text-sm text-white/75">Activo</label>
            @error('is_active')<p class="text-sm text-red-300">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex flex-wrap gap-3 border-t border-white/10 pt-5">
        <button type="submit" class="focus-cyan rounded-sm bg-cyan px-5 py-3 font-mono text-xs font-semibold tracking-wider text-void transition hover:brightness-110">{{ $editing ? 'GUARDAR CAMBIOS' : 'AGREGAR VIDEO' }}</button>
        <a href="{{ route('admin.videos.index') }}" class="focus-cyan rounded-sm border border-white/15 px-5 py-3 font-mono text-xs tracking-wider text-white/70 hover:border-white/30">CANCELAR</a>
    </div>
</form>
