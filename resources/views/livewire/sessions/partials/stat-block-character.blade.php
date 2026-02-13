@php
    $abilityMod = fn(int $score) => floor(($score - 10) / 2);
    $formatMod = fn(int $score) => ($m = $abilityMod($score)) >= 0 ? '+' . $m : (string) $m;
@endphp

<div class="space-y-3">
    {{-- Header --}}
    <div>
        <div class="text-lg font-bold text-blue-700 dark:text-blue-400">{{ $stat['name'] }}</div>
        <div class="text-xs text-zinc-500 dark:text-zinc-400">
            {{ __('Level') }} {{ $stat['level'] ?? '?' }} {{ $stat['class'] ?? '' }}
            @if (!empty($stat['player_name']))
                &mdash; {{ __('Player:') }} {{ $stat['player_name'] }}
            @endif
        </div>
    </div>

    <hr class="border-blue-300 dark:border-blue-800" />

    {{-- Core Stats --}}
    <div class="space-y-1 text-sm">
        <div><span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Armor Class') }}</span> {{ $stat['armor_class'] }}</div>
        <div><span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Hit Points') }}</span> {{ $stat['hp_max'] }}</div>
        @if (!empty($stat['alignment_label']))
            <div><span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Alignment') }}</span> {{ $stat['alignment_label'] }}</div>
        @endif
    </div>

    {{-- Ability Scores --}}
    @if (!empty($stat['stats']) && is_array($stat['stats']))
        <hr class="border-blue-300 dark:border-blue-800" />

        <div class="grid grid-cols-6 gap-1 text-center text-sm">
            @foreach (['strength' => 'STR', 'dexterity' => 'DEX', 'constitution' => 'CON', 'intelligence' => 'INT', 'wisdom' => 'WIS', 'charisma' => 'CHA'] as $key => $label)
                @if (isset($stat['stats'][$key]))
                    <div>
                        <div class="text-xs font-bold text-zinc-500 dark:text-zinc-400">{{ $label }}</div>
                        <div class="font-medium text-zinc-700 dark:text-zinc-200">{{ $stat['stats'][$key] }}</div>
                        <div class="text-xs text-zinc-400 dark:text-zinc-500">({{ $formatMod($stat['stats'][$key]) }})</div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    {{-- Notes --}}
    @if (!empty($stat['notes']))
        <hr class="border-blue-300 dark:border-blue-800" />
        <div class="text-sm">
            <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Notes') }}</span>
            <p class="mt-1 whitespace-pre-line text-zinc-600 dark:text-zinc-400">{{ $stat['notes'] }}</p>
        </div>
    @endif
</div>
