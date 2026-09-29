@php($editing = $testimonial->exists)

<form method="POST" action="{{ $editing ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}" enctype="multipart/form-data" class="mt-7 space-y-6">
    @csrf
    @if ($editing) @method('PUT') @endif

    @if ($errors->any())
        <div role="alert" class="rounded-sm border border-red-400/30 bg-red-400/10 p-4 text-sm text-red-200">
            <p class="font-medium">Revisa los campos marcados:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid gap-5 sm:grid-cols-2">
        <div><label for="name" class="mono-label mb-2 block text-white/65">Nombre *</label><input id="name" name="name" value="{{ old('name', $testimonial->name) }}" required maxlength="160" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">@error('name')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror</div>
        <div><label for="position_or_company" class="mono-label mb-2 block text-white/65">Cargo o empresa *</label><input id="position_or_company" name="position_or_company" value="{{ old('position_or_company', $testimonial->position_or_company) }}" required maxlength="180" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">@error('position_or_company')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror</div>
        <div class="sm:col-span-2"><label for="testimonial" class="mono-label mb-2 block text-white/65">Testimonio *</label><textarea id="testimonial" name="testimonial" rows="6" required maxlength="5000" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm leading-6 text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">{{ old('testimonial', $testimonial->testimonial) }}</textarea><p class="mt-2 text-xs text-white/40">Texto sin HTML; se mostrará escapado en el sitio.</p>@error('testimonial')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror</div>
        <div>
            <label for="photo" class="mono-label mb-2 block text-white/65">Fotografía {{ $editing ? '(opcional para conservar la actual)' : '*' }}</label>
            <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" @required(! $editing) class="block w-full rounded-sm border border-white/10 bg-black/35 p-3 text-sm text-white file:mr-4 file:rounded-sm file:border-0 file:bg-cyan file:px-3 file:py-2 file:font-mono file:text-xs file:font-semibold file:text-void">
            <p class="mt-2 text-xs text-white/45">JPG, PNG o WebP. Máximo 5 MB y 6000 × 6000 px.</p>
            @error('photo')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
            @if ($editing && $testimonial->photo)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($testimonial->photo) }}" alt="Fotografía actual de {{ $testimonial->name }}" class="mt-3 h-24 w-24 rounded-sm border border-white/10 object-cover">@endif
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div><label for="rating" class="mono-label mb-2 block text-white/65">Calificación · opcional</label><select id="rating" name="rating" class="w-full rounded-sm border border-white/10 bg-[#111217] px-4 py-3 text-sm text-white outline-none focus:border-cyan"><option value="">Sin calificación</option>@foreach (range(1, 5) as $rating)<option value="{{ $rating }}" @selected((string) old('rating', $testimonial->rating) === (string) $rating)>{{ $rating }} / 5</option>@endforeach</select>@error('rating')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror</div>
            <div><label for="position" class="mono-label mb-2 block text-white/65">Orden</label><input id="position" name="position" type="number" min="0" max="65535" step="1" required value="{{ old('position', $testimonial->position ?? 0) }}" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">@error('position')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror</div>
        </div>
    </div>

    <div class="flex items-center gap-3 border-t border-white/10 pt-5">
        <input type="hidden" name="is_active" value="0">
        <input id="is_active" name="is_active" type="checkbox" value="1" @checked((bool) old('is_active', $testimonial->is_active ?? false)) class="h-4 w-4 accent-cyan">
        <label for="is_active" class="text-sm text-white/75">Visible en el sitio público</label>
        @error('is_active')<p class="text-sm text-red-300">{{ $message }}</p>@enderror
    </div>

    <div class="flex flex-wrap gap-3 border-t border-white/10 pt-5">
        <button type="submit" class="focus-cyan rounded-sm bg-cyan px-5 py-3 font-mono text-xs font-semibold tracking-wider text-void transition hover:brightness-110">{{ $editing ? 'GUARDAR CAMBIOS' : 'CREAR TESTIMONIO' }}</button>
        <a href="{{ route('admin.testimonials.index') }}" class="focus-cyan rounded-sm border border-white/15 px-5 py-3 font-mono text-xs tracking-wider text-white/70 hover:border-white/30">CANCELAR</a>
    </div>
</form>
