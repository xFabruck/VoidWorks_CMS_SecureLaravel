@extends('layouts.admin')

@section('title', 'Biblioteca multimedia')
@section('breadcrumb', 'CONTENIDO / MULTIMEDIA')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="glass-panel relative flex flex-wrap items-end justify-between gap-5 overflow-hidden rounded-md p-5 sm:p-7">
            <div class="pointer-events-none absolute right-0 top-0 h-full w-1/3 bg-gradient-to-l from-cyan/[.04] to-transparent"></div>
            <div class="relative"><p class="mono-label text-cyan">// ARCHIVO DEL CMS</p><h1 class="mt-2 font-display text-2xl font-semibold text-white sm:text-3xl">Biblioteca <span class="text-cyan">multimedia</span></h1><p class="mt-2 max-w-2xl text-sm leading-6 text-muted">Imágenes y documentos almacenados de forma privada.</p></div>
            <p class="mono-label relative text-white/50">{{ $media->total() }} ARCHIVOS</p>
        </section>

        @if (session('status'))<div role="status" class="rounded-sm border border-emerald-400/30 bg-emerald-400/10 p-4 text-sm text-emerald-200">{{ session('status') }}</div>@endif
        @if ($errors->any())<div role="alert" class="rounded-sm border border-red-400/30 bg-red-400/10 p-4 text-sm text-red-200"><p class="font-semibold">No se pudo subir el archivo:</p><ul class="mt-2 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <section class="glass-panel rounded-md p-5 sm:p-6" aria-labelledby="upload-title">
            <h2 id="upload-title" class="font-display text-lg font-semibold text-white">Subir archivo</h2>
            <form id="media-upload-form" method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] lg:items-end">
                @csrf
                <div id="media-drop-zone" class="rounded-sm border border-dashed border-cyan/30 bg-black/20 p-4 transition hover:border-cyan/60">
                    <label for="media-file" class="mono-label block text-white/65">Archivo · JPG, JPEG, PNG, WebP o PDF · máximo 10 MB</label>
                    <input id="media-file" name="file" type="file" required accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf" class="mt-3 block w-full text-sm text-white/75 file:mr-4 file:rounded-sm file:border-0 file:bg-cyan file:px-3 file:py-2 file:font-mono file:text-xs file:font-semibold file:text-void">
                    <p id="media-file-name" class="mt-2 text-xs text-white/40">Arrastra un archivo aquí o selecciónalo.</p>
                    @error('file')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                    <div><label for="alt_text" class="mono-label mb-2 block text-white/65">Texto alternativo · opcional</label><input id="alt_text" name="alt_text" type="text" maxlength="255" value="{{ old('alt_text') }}" class="w-full rounded-sm border border-white/10 bg-black/30 px-3 py-2.5 text-sm text-white focus:border-cyan focus:outline-none">@error('alt_text')<p class="mt-1 text-sm text-red-300">{{ $message }}</p>@enderror</div>
                    <div><label for="caption" class="mono-label mb-2 block text-white/65">Descripción · opcional</label><input id="caption" name="caption" type="text" maxlength="2000" value="{{ old('caption') }}" class="w-full rounded-sm border border-white/10 bg-black/30 px-3 py-2.5 text-sm text-white focus:border-cyan focus:outline-none">@error('caption')<p class="mt-1 text-sm text-red-300">{{ $message }}</p>@enderror</div>
                </div>
                <button type="submit" class="focus-cyan min-h-11 rounded-sm bg-cyan px-5 py-3 font-mono text-xs font-semibold tracking-wider text-void transition hover:brightness-110">＋ SUBIR</button>
            </form>
        </section>

        <section aria-label="Archivos de la biblioteca" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @forelse ($media as $item)
                <article class="glass-panel overflow-hidden rounded-md">
                    <a href="{{ route('admin.media.file', $item) }}" target="_blank" rel="noopener noreferrer" class="focus-cyan relative grid aspect-[16/10] place-items-center overflow-hidden border-b border-white/10 bg-black/30" aria-label="Abrir {{ $item->name }}">
                        @if ($item->isImage())<img src="{{ route('admin.media.file', $item) }}" alt="{{ $item->alt_text ?? '' }}" class="h-full w-full object-cover">@else<span aria-hidden="true" class="font-mono text-5xl text-cyan/75">PDF</span>@endif
                        <span class="absolute bottom-2 left-2 rounded-sm border border-white/10 bg-black/75 px-2 py-1 mono-label text-white/70">{{ strtoupper($item->type) }}</span>
                    </a>
                    <div class="space-y-3 p-4">
                        <div class="min-w-0"><p class="truncate text-sm font-medium text-white" title="{{ $item->name }}">{{ $item->name }}.{{ $item->extension }}</p><p class="mt-1 text-xs text-white/45">{{ number_format($item->size / 1024, 1, ',', '.') }} KB · {{ $item->created_at->format('d/m/Y H:i') }}</p><p class="mt-1 truncate text-xs text-white/40">{{ $item->uploader?->name ?? 'Usuario eliminado' }}</p></div>
                        <form method="POST" action="{{ route('admin.media.destroy', $item) }}" data-confirm="¿Eliminar este archivo de la biblioteca?">
                            @csrf @method('DELETE')
                            @can('delete', $item)<button type="submit" class="focus-cyan rounded-sm border border-red-300/20 px-3 py-2 font-mono text-[.65rem] tracking-wider text-red-200 hover:bg-red-400/10">ELIMINAR</button>@endcan
                        </form>
                    </div>
                </article>
            @empty
                <div class="glass-panel rounded-md p-10 text-center text-sm text-white/50 sm:col-span-2 xl:col-span-3 2xl:col-span-4">La biblioteca todavía está vacía. Sube una imagen o un documento para comenzar.</div>
            @endforelse
        </section>

        @if ($media->hasPages())<div>{{ $media->links() }}</div>@endif
    </div>

@endsection
