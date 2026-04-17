@props(['media'])

@php
    $media = $media ?? collect();
    $items = $media->values();
    $images = $items->filter(fn ($m) => $m->type?->value === 'image');
    $videos = $items->filter(fn ($m) => $m->type?->value === 'video');
@endphp

@if ($items->isEmpty())
    @php(return) @endphp
@endif

<div
    x-data="{
        open: false,
        src: null,
        show(url) { this.src = url; this.open = true; },
        close() { this.open = false; this.src = null; },
    }"
    class="space-y-3"
>
    @if ($videos->isNotEmpty())
        @foreach ($videos as $video)
            <video controls class="w-full rounded-lg bg-black">
                <source src="{{ $video->file_url }}" />
            </video>
        @endforeach
    @endif

    @if ($images->isNotEmpty())
        @php
            $count = $images->count();
            $visible = $images->take(4)->values();
            $extra = max(0, $count - 4);
        @endphp

        @if ($count === 1)
            <button type="button" class="block w-full" @click="show('{{ $visible[0]->file_url }}')">
                <img src="{{ $visible[0]->file_url }}" alt="" class="w-full rounded-lg object-cover" loading="lazy" />
            </button>
        @elseif ($count === 2)
            <div class="grid grid-cols-2 gap-2">
                @foreach ($visible as $img)
                    <button type="button" class="block" @click="show('{{ $img->file_url }}')">
                        <img src="{{ $img->file_url }}" alt="" class="aspect-[4/3] w-full rounded-lg object-cover" loading="lazy" />
                    </button>
                @endforeach
            </div>
        @else
            <div class="grid grid-cols-2 gap-2">
                @foreach ($visible as $idx => $img)
                    <button type="button" class="relative block" @click="show('{{ $img->file_url }}')">
                        <img src="{{ $img->file_url }}" alt="" class="aspect-[4/3] w-full rounded-lg object-cover" loading="lazy" />

                        @if ($idx === 3 && $extra > 0)
                            <div class="absolute inset-0 flex items-center justify-center rounded-lg bg-black/60 text-sm font-semibold text-white">
                                +{{ $extra }} more
                            </div>
                        @endif
                    </button>
                @endforeach
            </div>
        @endif
    @endif

    <div
        x-show="open"
        x-cloak
        @keydown.escape.window="close()"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
    >
        <button type="button" class="absolute inset-0" @click="close()"></button>

        <div class="relative z-10 max-w-4xl">
            <button type="button" class="absolute -right-2 -top-10 text-sm font-medium text-white/90 hover:text-white" @click="close()">
                Close
            </button>
            <img :src="src" alt="" class="max-h-[80vh] w-auto rounded-lg shadow-2xl" />
        </div>
    </div>
</div>

