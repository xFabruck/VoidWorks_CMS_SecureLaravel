@extends('layouts.admin')

@section('title', 'Configuración SEO')
@section('breadcrumb', 'CONFIGURACIÓN / SEO')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <section class="glass-panel rounded-md p-5 sm:p-8">
            <p class="mono-label text-cyan">// POSICIONAMIENTO Y PREVISUALIZACIÓN</p>
            <h1 class="mt-2 font-display text-2xl font-semibold text-white sm:text-3xl">Configuración <span class="text-cyan">SEO</span></h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-muted">Define los valores generales que se usan cuando una página no tiene metadatos propios. Las publicaciones y servicios conservan sus campos SEO individuales.</p>
        </section>

        @if (session('status'))<div role="status" class="rounded-sm border border-emerald-400/30 bg-emerald-400/10 p-4 text-sm text-emerald-200">{{ session('status') }}</div>@endif

        <section class="glass-panel rounded-md p-5 sm:p-8">
            <form method="POST" action="{{ route('admin.seo.update') }}" class="space-y-6">
                @csrf @method('PUT')
                @if ($errors->any())
                    <div role="alert" class="rounded-sm border border-red-400/30 bg-red-400/10 p-4 text-sm text-red-200">
                        <p class="font-medium">Revisa la configuración:</p>
                        <ul class="mt-2 list-inside list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <div>
                    <label for="site_title" class="mono-label mb-2 block text-white/65">Título del sitio *</label>
                    <input id="site_title" name="site_title" value="{{ old('site_title', $settings->site_title) }}" required maxlength="180" aria-describedby="site_title-error" @class(['w-full rounded-sm border bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15', 'border-red-400/70' => $errors->has('site_title'), 'border-white/10' => ! $errors->has('site_title')])>
                    @error('site_title')<p id="site_title-error" role="alert" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="default_meta_description" class="mono-label mb-2 block text-white/65">Meta descripción general *</label>
                    <textarea id="default_meta_description" name="default_meta_description" rows="3" required maxlength="300" aria-describedby="default_meta_description-error" @class(['w-full rounded-sm border bg-black/35 px-4 py-3 text-sm leading-6 text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15', 'border-red-400/70' => $errors->has('default_meta_description'), 'border-white/10' => ! $errors->has('default_meta_description')])>{{ old('default_meta_description', $settings->default_meta_description) }}</textarea>
                    <p class="mt-2 text-xs text-white/45">Se usa cuando la página no tiene una descripción propia.</p>
                    @error('default_meta_description')<p id="default_meta_description-error" role="alert" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="default_og_image" class="mono-label mb-2 block text-white/65">Imagen Open Graph predeterminada</label>
                    <input id="default_og_image" name="default_og_image" type="url" inputmode="url" value="{{ old('default_og_image', $settings->default_og_image) }}" maxlength="2048" placeholder="https://sitio.example/imagen.jpg" aria-describedby="default_og_image-help default_og_image-error" @class(['w-full rounded-sm border bg-black/35 px-4 py-3 text-sm text-white outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/15', 'border-red-400/70' => $errors->has('default_og_image'), 'border-white/10' => ! $errors->has('default_og_image')])>
                    <p id="default_og_image-help" class="mt-2 text-xs text-white/45">URL HTTPS pública a una imagen JPG, PNG o WebP. Debe ser accesible sin iniciar sesión.</p>
                    @error('default_og_image')<p id="default_og_image-error" role="alert" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                </div>

                <fieldset class="space-y-4 border-t border-white/10 pt-5">
                    <legend class="mono-label text-white/65">Directivas para buscadores</legend>
                    <div class="flex items-start gap-3">
                        <input type="hidden" name="robots_index" value="0">
                        <input id="robots_index" name="robots_index" type="checkbox" value="1" @checked((bool) old('robots_index', $settings->robots_index)) class="mt-1 h-4 w-4 accent-cyan">
                        <label for="robots_index"><span class="block text-sm text-white/80">Permitir indexación</span><span class="mt-1 block text-xs text-white/45">Desactívala para pedir que los buscadores no incluyan el sitio en resultados.</span></label>
                    </div>
                    <div class="flex items-start gap-3">
                        <input type="hidden" name="robots_follow" value="0">
                        <input id="robots_follow" name="robots_follow" type="checkbox" value="1" @checked((bool) old('robots_follow', $settings->robots_follow)) class="mt-1 h-4 w-4 accent-cyan">
                        <label for="robots_follow"><span class="block text-sm text-white/80">Seguir enlaces</span><span class="mt-1 block text-xs text-white/45">Controla si los rastreadores pueden seguir los enlaces de las páginas.</span></label>
                    </div>
                    @error('robots_index')<p role="alert" class="text-sm text-red-300">{{ $message }}</p>@enderror
                    @error('robots_follow')<p role="alert" class="text-sm text-red-300">{{ $message }}</p>@enderror
                </fieldset>

                <div class="flex flex-wrap gap-3 border-t border-white/10 pt-5">
                    <button type="submit" class="focus-cyan rounded-sm bg-cyan px-5 py-3 font-mono text-xs font-semibold tracking-wider text-void transition hover:brightness-110">GUARDAR CONFIGURACIÓN</button>
                </div>
            </form>
        </section>
    </div>
@endsection
