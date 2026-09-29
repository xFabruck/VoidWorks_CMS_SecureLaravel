<article class="px-5 py-14 sm:py-20 lg:px-10">
    <div class="mx-auto max-w-4xl">
        <a href="{{ route('news.index') }}" class="focus-cyan mono-label text-white/55 hover:text-cyan">← TODAS LAS NOTICIAS</a>
        <header class="mt-7">
            <div class="flex flex-wrap items-center gap-3 mono-label text-white/45"><time datetime="{{ $post->published_at?->timezone($siteSettings->presentation_timezone)->toIso8601String() }}">{{ $post->published_at?->timezone($siteSettings->presentation_timezone)->format('d/m/Y') ?? 'Vista previa' }}</time>@if ($post->category)<span class="text-cyan">{{ $post->category->name }}</span>@endif</div>
            <h1 class="mt-4 font-display text-4xl font-bold leading-tight tracking-tight text-white sm:text-5xl">{{ $post->title }}</h1>
            @if ($post->excerpt)<p class="mt-5 whitespace-pre-line font-display text-xl leading-8 text-white/70">{{ $post->excerpt }}</p>@endif
            <p class="mt-5 text-sm text-white/45">{{ $post->author?->name }}</p>
        </header>
        @if ($post->featured_image)<div class="mt-8 overflow-hidden rounded-md border border-white/10 bg-panel"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->featured_image) }}" alt="{{ $post->title }}" class="max-h-[38rem] w-full object-cover"></div>@endif
        <div class="mt-9 whitespace-pre-line text-base leading-8 text-muted">{{ $post->content }}</div>
    </div>
</article>
