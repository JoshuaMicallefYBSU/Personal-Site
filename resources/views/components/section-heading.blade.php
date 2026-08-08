@props(['eyebrow' => null, 'title', 'color' => 'indigo', 'align' => 'left'])

@php
    $palette = [
        'indigo' => ['text' => 'text-indigo-600 dark:text-indigo-400', 'bar' => 'from-indigo-500 to-violet-400'],
        'violet' => ['text' => 'text-violet-600 dark:text-violet-400', 'bar' => 'from-violet-500 to-fuchsia-400'],
        'amber' => ['text' => 'text-amber-600 dark:text-amber-400', 'bar' => 'from-amber-500 to-orange-400'],
        'emerald' => ['text' => 'text-emerald-600 dark:text-emerald-400', 'bar' => 'from-emerald-500 to-teal-400'],
        'rose' => ['text' => 'text-rose-600 dark:text-rose-400', 'bar' => 'from-rose-500 to-pink-400'],
    ];
    $c = $palette[$color] ?? $palette['indigo'];
    $isCenter = $align === 'center';
@endphp

<div class="reveal mb-10 {{ $isCenter ? 'flex flex-col items-center text-center' : '' }}">
    <div class="mb-4 h-1 w-10 rounded-full bg-gradient-to-r {{ $c['bar'] }}"></div>
    @if ($eyebrow)
        <p class="mb-1.5 text-xs font-bold tracking-[0.2em] uppercase {{ $c['text'] }}">{{ $eyebrow }}</p>
    @endif
    <h2 class="text-2xl font-bold tracking-tight text-zinc-900 sm:text-3xl dark:text-white">{{ $title }}</h2>
</div>
