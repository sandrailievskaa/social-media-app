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
            class="inline-flex items-center gap-1 rounded-full border px-3 py-1 text-sm transition
                {{ $active === $type ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}"
        >
            <span aria-hidden="true">{{ $emoji }}</span>
            <span class="tabular-nums">{{ $count }}</span>
        </button>
    @endforeach
</div>

