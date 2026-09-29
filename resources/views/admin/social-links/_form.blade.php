@php($editing = $socialLink->exists)

<form method="POST" action="{{ $editing ? route('admin.social-links.update', $socialLink) : route('admin.social-links.store') }}" class="mt-7 space-y-6">
    @csrf
    @if ($editing) @method('PUT') @endif

    @if ($errors->any())
        <div role="alert" class="rounded-sm border border-red-400/30 bg-red-400/10 p-4 text-sm text-red-200">
            <p class="font-medium">Revisa los campos marcados:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="network" class="mono-label mb-2 block text-white/65">Red social *</label>
            <select id="network" name="network" required class="w-full rounded-sm border border-white/10 bg-[#111217] px-4 py-3 text-sm text-white outline-none focus:border-cyan">
                @foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube', 'tiktok' => 'TikTok', 'x' => 'X', 'whatsapp' => 'WhatsApp', 'other' => 'Otra'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('network', $socialLink->network) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('network')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="mono-label mb-2 block text-white/65">Icono</label>
            <div data-social-icon-preview class="flex min-h-[46px] items-center gap-3 rounded-sm border border-white/10 bg-black/20 px-3 text-sm text-white/60">@include('components.social-icon', ['icon' => old('network', $socialLink->network ?? 'other') === 'other' ? 'globe' : old('network', $socialLink->icon)])<span>Asignado según la red seleccionada</span></div>
        </div>
        <div class="sm:col-span-2">
            <label for="url" class="mono-label mb-2 block text-white/65">URL del perfil *</label>
            <input id="url" name="url" type="url" inputmode="url" value="{{ old('url', $socialLink->url) }}" required maxlength="2048" placeholder="https://…" aria-describedby="url-help url-error" @class(['w-full rounded-sm border bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15', 'border-red-400/70' => $errors->has('url'), 'border-white/10' => ! $errors->has('url')])>
            <p id="url-help" class="mt-2 text-xs text-white/45">Usa HTTPS. Para las redes conocidas se comprueba que el dominio corresponda a la plataforma.</p>
            @error('url')<p id="url-error" role="alert" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="position" class="mono-label mb-2 block text-white/65">Orden</label>
            <input id="position" name="position" type="number" min="0" max="65535" step="1" required value="{{ old('position', $socialLink->position ?? 0) }}" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">
            @error('position')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-center gap-3 self-center">
            <input type="hidden" name="is_active" value="0">
            <input id="is_active" name="is_active" type="checkbox" value="1" @checked((bool) old('is_active', $socialLink->is_active ?? false)) class="h-4 w-4 accent-cyan">
            <label for="is_active" class="text-sm text-white/75">Mostrar en el sitio público</label>
            @error('is_active')<p class="text-sm text-red-300">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex flex-wrap gap-3 border-t border-white/10 pt-5">
        <button type="submit" class="focus-cyan rounded-sm bg-cyan px-5 py-3 font-mono text-xs font-semibold tracking-wider text-void transition hover:brightness-110">{{ $editing ? 'GUARDAR CAMBIOS' : 'AGREGAR RED' }}</button>
        <a href="{{ route('admin.social-links.index') }}" class="focus-cyan rounded-sm border border-white/15 px-5 py-3 font-mono text-xs tracking-wider text-white/70 hover:border-white/30">CANCELAR</a>
    </div>
</form>
