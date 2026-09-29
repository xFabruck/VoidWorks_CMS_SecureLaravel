@extends('layouts.admin')

@section('title', 'Nosotros / Institucional')
@section('heading', 'Nosotros / Institucional')
@section('breadcrumb', 'CONTENIDO / NOSOTROS')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <section class="glass-panel rounded-md p-5 sm:p-7">
            <p class="mono-label text-cyan">// PÁGINA INSTITUCIONAL</p>
            <h2 class="mt-2 font-display text-2xl font-semibold text-white sm:text-3xl">Nosotros <span class="text-cyan">/ Institucional</span></h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-muted">Gestiona el contenido que se muestra en la página pública Nosotros. El registro se mantiene único.</p>
        </section>

        @if (session('status'))<div role="status" class="rounded-sm border border-emerald-400/30 bg-emerald-400/10 p-4 text-sm text-emerald-200">{{ session('status') }}</div>@endif

        <section class="glass-panel rounded-md p-5 sm:p-8">
            @if ($errors->any())
                <div role="alert" class="mb-6 rounded-sm border border-red-400/30 bg-red-400/10 p-4 text-sm text-red-200"><p class="font-medium">Revisa los campos marcados:</p><ul class="mt-2 list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form method="POST" action="{{ route('admin.about.update') }}" enctype="multipart/form-data" class="space-y-8">
                @csrf @method('PUT')
                <section class="space-y-5" aria-labelledby="about-general-heading">
                    <h3 id="about-general-heading" class="mono-label border-b border-white/10 pb-3 text-cyan">01 // INFORMACIÓN PRINCIPAL</h3>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div><label for="title" class="mono-label mb-2 block text-white/65">Título *</label><input id="title" name="title" value="{{ old('title', $aboutPage->title) }}" required maxlength="160" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">@error('title')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror</div>
                        <div><label for="subtitle" class="mono-label mb-2 block text-white/65">Subtítulo</label><input id="subtitle" name="subtitle" value="{{ old('subtitle', $aboutPage->subtitle) }}" maxlength="220" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">@error('subtitle')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror</div>
                        <div class="sm:col-span-2"><label for="content" class="mono-label mb-2 block text-white/65">Contenido * <span class="text-white/35">(texto plano; se mostrará escapado)</span></label><textarea id="content" name="content" required maxlength="30000" rows="7" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm leading-6 text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">{{ old('content', $aboutPage->content) }}</textarea>@error('content')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror</div>
                        <div><label for="primary_image" class="mono-label mb-2 block text-white/65">Imagen principal</label><input id="primary_image" name="primary_image" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full rounded-sm border border-white/10 bg-black/35 p-3 text-sm text-white file:mr-4 file:rounded-sm file:border-0 file:bg-cyan file:px-3 file:py-2 file:font-mono file:text-xs file:font-semibold file:text-void" aria-describedby="primary-image-help"> <p id="primary-image-help" class="mt-2 text-xs text-white/45">JPG, PNG o WebP; máximo 5 MB y 6000 × 6000 px. Si no seleccionas otra, se conserva la actual.</p>@error('primary_image')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                            @if ($aboutPage->primary_image)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($aboutPage->primary_image) }}" alt="{{ $aboutPage->title }}" class="mt-3 h-28 w-48 rounded-sm border border-white/10 object-cover">@endif
                        </div>
                    </div>
                </section>

                <section class="space-y-5" aria-labelledby="about-purpose-heading">
                    <h3 id="about-purpose-heading" class="mono-label border-b border-white/10 pb-3 text-cyan">02 // IDENTIDAD</h3>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div><label for="mission" class="mono-label mb-2 block text-white/65">Misión *</label><textarea id="mission" name="mission" required maxlength="12000" rows="5" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm leading-6 text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">{{ old('mission', $aboutPage->mission) }}</textarea>@error('mission')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror</div>
                        <div><label for="vision" class="mono-label mb-2 block text-white/65">Visión *</label><textarea id="vision" name="vision" required maxlength="12000" rows="5" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm leading-6 text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">{{ old('vision', $aboutPage->vision) }}</textarea>@error('vision')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror</div>
                        <div class="sm:col-span-2"><label for="values" class="mono-label mb-2 block text-white/65">Valores *</label><textarea id="values" name="values" required maxlength="12000" rows="5" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm leading-6 text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">{{ old('values', $aboutPage->values) }}</textarea>@error('values')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror</div>
                    </div>
                </section>

                <section class="space-y-5" aria-labelledby="about-history-heading">
                    <h3 id="about-history-heading" class="mono-label border-b border-white/10 pb-3 text-cyan">03 // HISTORIA</h3>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div><label for="history" class="mono-label mb-2 block text-white/65">Historia *</label><textarea id="history" name="history" required maxlength="30000" rows="8" class="w-full rounded-sm border border-white/10 bg-black/35 px-4 py-3 text-sm leading-6 text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15">{{ old('history', $aboutPage->history) }}</textarea>@error('history')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror</div>
                        <div><label for="secondary_image" class="mono-label mb-2 block text-white/65">Imagen secundaria</label><input id="secondary_image" name="secondary_image" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full rounded-sm border border-white/10 bg-black/35 p-3 text-sm text-white file:mr-4 file:rounded-sm file:border-0 file:bg-cyan file:px-3 file:py-2 file:font-mono file:text-xs file:font-semibold file:text-void" aria-describedby="secondary-image-help"><p id="secondary-image-help" class="mt-2 text-xs text-white/45">JPG, PNG o WebP; máximo 5 MB y 6000 × 6000 px. Si no seleccionas otra, se conserva la actual.</p>@error('secondary_image')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                            @if ($aboutPage->secondary_image)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($aboutPage->secondary_image) }}" alt="{{ $aboutPage->title }}" class="mt-3 h-28 w-48 rounded-sm border border-white/10 object-cover">@endif
                        </div>
                    </div>
                </section>

                <div class="flex flex-wrap items-center justify-between gap-4 border-t border-white/10 pt-5">
                    <label for="is_active" class="flex items-center gap-3 text-sm text-white/75"><input type="hidden" name="is_active" value="0"><input id="is_active" name="is_active" type="checkbox" value="1" @checked((bool) old('is_active', $aboutPage->is_active ?? false)) class="h-4 w-4 accent-cyan"> Publicar página institucional</label>
                    <button type="submit" class="focus-cyan rounded-sm bg-cyan px-5 py-3 font-mono text-xs font-semibold tracking-wider text-void transition hover:brightness-110">GUARDAR INFORMACIÓN</button>
                </div>
            </form>
        </section>
    </div>
@endsection
