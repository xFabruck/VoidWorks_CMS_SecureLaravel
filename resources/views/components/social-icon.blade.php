@php($symbol = match ($icon) {
    'facebook' => 'f',
    'instagram' => '◎',
    'linkedin' => 'in',
    'youtube' => '▶',
    'tiktok' => '♪',
    'x' => '𝕏',
    'whatsapp' => '☎',
    default => '↗',
})
<span aria-hidden="true" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-current/25 font-display text-sm font-semibold normal-case">{{ $symbol }}</span>
