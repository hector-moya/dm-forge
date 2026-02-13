@php
    $abilityMod = fn(int $score) => floor(($score - 10) / 2);
    $formatMod = fn(int $score) => ($m = $abilityMod($score)) >= 0 ? '+' . $m : (string) $m;
@endphp

<div class="space-y-3">
    {{-- Header --}}
    <div>
        <div class="text-lg font-bold text-emerald-700 dark:text-emerald-400">{{ $stat['name'] }}</div>
        @if (!empty($stat['role']))
            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $stat['role'] }}</div>
        @endif
    </div>

    <hr class="border-emerald-300 dark:border-emerald-800" />

    {{-- Details --}}
    <div class="space-y-2 text-sm">
        @if (!empty($stat['description']))
            <div>
                <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Description') }}</span>
                <p class="mt-0.5 text-zinc-600 dark:text-zinc-400">{{ $stat['description'] }}</p>
            </div>
        @endif

        @if (!empty($stat['personality']))
            <div>
                <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Personality') }}</span>
                <p class="mt-0.5 text-zinc-600 dark:text-zinc-400">{{ $stat['personality'] }}</p>
            </div>
        @endif

        @if (!empty($stat['motivation']))
            <div>
                <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Motivation') }}</span>
                <p class="mt-0.5 text-zinc-600 dark:text-zinc-400">{{ $stat['motivation'] }}</p>
            </div>
        @endif

        @if (!empty($stat['faction']))
            <div>
                <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Faction') }}</span>
                <span class="text-zinc-600 dark:text-zinc-400">{{ $stat['faction'] }}</span>
            </div>
        @endif

        @if (!empty($stat['location']))
            <div>
                <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Location') }}</span>
                <span class="text-zinc-600 dark:text-zinc-400">{{ $stat['location'] }}</span>
            </div>
        @endif
    </div>

    {{-- Ability Scores --}}
    @if (!empty($stat['stats']) && is_array($stat['stats']))
        <hr class="border-emerald-300 dark:border-emerald-800" />

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
</div>
