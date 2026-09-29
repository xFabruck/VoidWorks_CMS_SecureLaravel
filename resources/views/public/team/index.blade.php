@extends('layouts.public')

@section('title', 'Equipo')
@section('meta_description', 'Conoce al equipo de '.config('app.name').'.')

@section('content')
    <section class="px-5 py-16 sm:py-20 lg:px-10">
        <div class="mx-auto max-w-7xl">
            <p class="mono-label text-cyan">// LAS PERSONAS DETRÁS DEL ESTUDIO</p>
            <h1 class="mt-3 font-display text-4xl font-bold tracking-tight text-white sm:text-5xl">Nuestro equipo</h1>
            @if ($teamMembers->isNotEmpty())
                <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($teamMembers as $teamMember)
                        <article class="glass-panel flex h-full flex-col overflow-hidden rounded-md">
                            @if ($teamMember->show_photo && $teamMember->photo)<img src="{{ route('team.photo', $teamMember) }}" alt="Fotografía de {{ $teamMember->name }}" loading="lazy" class="aspect-[4/3] w-full object-cover">@endif
                            <div class="flex flex-1 flex-col p-5 sm:p-6">
                                <h2 class="font-display text-xl font-semibold text-white">{{ $teamMember->name }}</h2>
                                <p class="mt-1 mono-label text-cyan">{{ $teamMember->position }}</p>
                                @if ($teamMember->show_biography && $teamMember->biography)<p class="mt-4 whitespace-pre-line text-sm leading-6 text-muted">{{ $teamMember->biography }}</p>@endif
                                @if (($teamMember->show_email && $teamMember->email) || ($teamMember->show_phone && $teamMember->phone) || ($teamMember->show_linkedin_url && $teamMember->linkedin_url))
                                    <address class="mt-5 flex flex-wrap gap-x-4 gap-y-2 border-t border-white/10 pt-4 text-sm not-italic text-white/65">
                                        @if ($teamMember->show_email && $teamMember->email)<a href="mailto:{{ $teamMember->email }}" class="focus-cyan hover:text-cyan">Correo</a>@endif
                                        @if ($teamMember->show_phone && $teamMember->phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $teamMember->phone) }}" class="focus-cyan hover:text-cyan">Teléfono</a>@endif
                                        @if ($teamMember->show_linkedin_url && $teamMember->linkedin_url)<a href="{{ $teamMember->linkedin_url }}" target="_blank" rel="noopener noreferrer" class="focus-cyan hover:text-cyan">LinkedIn ↗</a>@endif
                                    </address>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="glass-panel mt-10 rounded-md px-6 py-14 text-center"><p class="mono-label text-white/40">EQUIPO // EN PREPARACIÓN</p><p class="mt-3 text-sm text-muted">Pronto compartiremos más información sobre nuestro equipo.</p></div>
            @endif
        </div>
    </section>
@endsection
