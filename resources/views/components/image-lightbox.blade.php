@props(['src', 'alt' => '', 'class' => 'h-48 w-full rounded-lg object-cover'])

<div x-data="{ showFullImage: false }">
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        class="{{ $class }} cursor-zoom-in transition hover:opacity-80"
        loading="lazy"
        @click="showFullImage = true"
    />

    <div
        x-show="showFullImage"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="showFullImage = false"
        @keydown.escape.window="showFullImage = false"
        class="fixed inset-0 z-99 flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm"
    >
        <img
            src="{{ $src }}"
            alt="{{ $alt }}"
            class="max-h-[90vh] max-w-[90vw] rounded-xl object-contain shadow-2xl"
            @click.stop
        />
        <button @click="showFullImage = false" class="absolute right-6 top-6 rounded-full bg-black/50 p-2 text-white transition hover:bg-black/70">
            <flux:icon name="x-mark" class="size-6" />
        </button>
    </div>
</div>
