@php($editing = $teamMember->exists)

<form method="POST" action="{{ $editing ? route('admin.team.update', $teamMember) : route('admin.team.store') }}" enctype="multipart/form-data" class="mt-7 space-y-6">
    @csrf
    @if ($editing) @method('PUT') @endif

    @if ($errors->any())
        <div role="alert" class="rounded-sm border border-red-400/30 bg-red-400/10 p-4 text-sm text-red-200">
            <p class="font-medium">Revisa los campos marcados:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid gap-5 sm:grid-cols-2">
        <div><label for="name" class="mono-label mb-2 block text-white/65">Nombre *</label><input id="name" name="name" value="{{ old('name', $teamMember->name) }}" required maxlength="160" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">@error('name')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror</div>
        <div><label for="position" class="mono-label mb-2 block text-white/65">Cargo *</label><input id="position" name="position" value="{{ old('position', $teamMember->position) }}" required maxlength="160" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">@error('position')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror</div>
        <div class="sm:col-span-2"><label for="biography" class="mono-label mb-2 block text-white/65">Biografía</label><textarea id="biography" name="biography" rows="5" maxlength="5000" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm leading-6 text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">{{ old('biography', $teamMember->biography) }}</textarea>@error('biography')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror</div>
        <div>
            <label for="photo" class="mono-label mb-2 block text-white/65">Fotografía · opcional</label>
            <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full rounded-sm border border-white/10 bg-black/35 p-3 text-sm text-white file:mr-4 file:rounded-sm file:border-0 file:bg-cyan file:px-3 file:py-2 file:font-mono file:text-xs file:font-semibold file:text-void">
            <p class="mt-2 text-xs text-white/45">JPG, PNG o WebP. Máximo 5 MB y 6000 × 6000 px. El archivo recibe un nombre seguro.</p>
            @error('photo')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
            @if ($editing && $teamMember->photo)<img src="{{ route('admin.team.photo', $teamMember) }}" alt="Fotografía actual de {{ $teamMember->name }}" class="mt-3 h-28 w-28 rounded-sm border border-white/10 object-cover">@endif
        </div>
        <div class="grid gap-5">
            <div><label for="email" class="mono-label mb-2 block text-white/65">Correo electrónico · opcional</label><input id="email" name="email" type="email" value="{{ old('email', $teamMember->email) }}" maxlength="254" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">@error('email')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror</div>
            <div><label for="phone" class="mono-label mb-2 block text-white/65">Teléfono · opcional</label><input id="phone" name="phone" type="tel" value="{{ old('phone', $teamMember->phone) }}" maxlength="32" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">@error('phone')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror</div>
            <div><label for="linkedin_url" class="mono-label mb-2 block text-white/65">Perfil de LinkedIn · opcional</label><input id="linkedin_url" name="linkedin_url" type="url" value="{{ old('linkedin_url', $teamMember->linkedin_url) }}" maxlength="2048" placeholder="https://www.linkedin.com/in/..." class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">@error('linkedin_url')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror</div>
        </div>
        <div><label for="position_order" class="mono-label mb-2 block text-white/65">Orden</label><input id="position_order" name="position_order" type="number" min="0" max="65535" step="1" required value="{{ old('position_order', $teamMember->position_order ?? 0) }}" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">@error('position_order')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror</div>
    </div>

    <fieldset class="rounded-sm border border-white/10 bg-black/15 p-4 sm:p-5">
        <legend class="px-2 mono-label text-cyan">VISIBILIDAD PÚBLICA</legend>
        <p class="mb-4 text-xs leading-5 text-white/50">Nombre y cargo se muestran para los miembros activos. El resto de los datos es privado hasta que lo habilites aquí.</p>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                'show_photo' => 'Mostrar fotografía',
                'show_biography' => 'Mostrar biografía',
                'show_email' => 'Mostrar correo',
                'show_phone' => 'Mostrar teléfono',
                'show_linkedin_url' => 'Mostrar perfil de LinkedIn',
            ] as $field => $label)
                <label for="{{ $field }}" class="flex items-center gap-3 rounded-sm border border-white/5 px-3 py-3 text-sm text-white/75">
                    <input type="hidden" name="{{ $field }}" value="0">
                    <input id="{{ $field }}" name="{{ $field }}" type="checkbox" value="1" @checked((bool) old($field, $teamMember->{$field} ?? false)) class="h-4 w-4 accent-cyan">
                    {{ $label }}
                </label>
                @error($field)<p class="text-sm text-red-300">{{ $message }}</p>@enderror
            @endforeach
        </div>
    </fieldset>

    <div class="flex items-center gap-3 border-t border-white/10 pt-5">
        <input type="hidden" name="is_active" value="0">
        <input id="is_active" name="is_active" type="checkbox" value="1" @checked((bool) old('is_active', $teamMember->is_active ?? false)) class="h-4 w-4 accent-cyan">
        <label for="is_active" class="text-sm text-white/75">Miembro activo en el sitio público</label>
        @error('is_active')<p class="text-sm text-red-300">{{ $message }}</p>@enderror
    </div>

    <div class="flex flex-wrap gap-3 border-t border-white/10 pt-5">
        <button type="submit" class="focus-cyan rounded-sm bg-cyan px-5 py-3 font-mono text-xs font-semibold tracking-wider text-void transition hover:brightness-110">{{ $editing ? 'GUARDAR CAMBIOS' : 'AGREGAR MIEMBRO' }}</button>
        <a href="{{ route('admin.team.index') }}" class="focus-cyan rounded-sm border border-white/15 px-5 py-3 font-mono text-xs tracking-wider text-white/70 hover:border-white/30">CANCELAR</a>
    </div>
</form>
