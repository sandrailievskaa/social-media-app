@props([
    'counts' => [],
    'active' => null,
])

@php
    $reactions = [
        'like' => '👍',
        'love' => '❤️',
        'laugh' => '😂',
        'wow' => '😮',
        'sad' => '😢',
        'angry' => '😡',
    ];
@endphp

<div class="flex flex-wrap items-center gap-2">
    @foreach ($reactions as $type => $emoji)
        @php($count = (int) ($counts[$type] ?? 0))
        <button
            type="button"
            wire:click="toggleReaction('{{ $type }}')"
            class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-sm font-semibold transition-all duration-200 active:scale-95
                {{ $active === $type ? 'border-indigo-200 bg-indigo-50 text-indigo-700' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-100' }}"
        >
            <span aria-hidden="true">{{ $emoji }}</span>
            <span class="tabular-nums">{{ $count }}</span>
        </button>
    @endforeach
</div>

