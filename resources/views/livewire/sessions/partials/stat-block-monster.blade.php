@php
    $abilityMod = fn(int $score) => floor(($score - 10) / 2);
    $formatMod = fn(int $score) => ($m = $abilityMod($score)) >= 0 ? '+' . $m : (string) $m;
@endphp

<div class="space-y-3">
    {{-- Image --}}
    @if (!empty($stat['image_url']))
        <div class="flex justify-center">
            <img src="{{ $stat['image_url'] }}" alt="{{ $stat['name'] }}" class="max-h-40 rounded-lg object-contain" loading="lazy" />
        </div>
    @endif

    {{-- Header --}}
    <div>
        <div class="text-lg font-bold text-red-700 dark:text-red-400">{{ $stat['name'] }}</div>
        <div class="text-xs italic text-zinc-500 dark:text-zinc-400">
            {{ $stat['size'] ?? '' }} {{ $stat['monster_type'] ?? '' }}{{ !empty($stat['subtype']) ? ' (' . $stat['subtype'] . ')' : '' }}{{ !empty($stat['alignment']) ? ', ' . $stat['alignment'] : '' }}
        </div>
    </div>

    <hr class="border-red-300 dark:border-red-800" />

    {{-- Core Stats --}}
    <div class="space-y-1 text-sm">
        <div><span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Armor Class') }}</span> {{ $stat['armor_class'] }}{{ !empty($stat['armor_class_type']) ? ' (' . $stat['armor_class_type'] . ')' : '' }}</div>
        <div><span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Hit Points') }}</span> {{ $stat['hit_points'] }}{{ !empty($stat['hit_dice']) ? ' (' . $stat['hit_dice'] . ')' : '' }}</div>
        @if (!empty($stat['speed']))
            <div>
                <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Speed') }}</span>
                @if (is_array($stat['speed']))
                    {{ implode(', ', array_map(fn($k, $v) => $k . ' ' . $v, array_keys($stat['speed']), $stat['speed'])) }}
                @else
                    {{ $stat['speed'] }}
                @endif
            </div>
        @endif
    </div>

    <hr class="border-red-300 dark:border-red-800" />

    {{-- Ability Scores --}}
    <div class="grid grid-cols-6 gap-1 text-center text-sm">
        @foreach (['strength' => 'STR', 'dexterity' => 'DEX', 'constitution' => 'CON', 'intelligence' => 'INT', 'wisdom' => 'WIS', 'charisma' => 'CHA'] as $key => $label)
            <div>
                <div class="text-xs font-bold text-zinc-500 dark:text-zinc-400">{{ $label }}</div>
                <div class="font-medium text-zinc-700 dark:text-zinc-200">{{ $stat[$key] }}</div>
                <div class="text-xs text-zinc-400 dark:text-zinc-500">({{ $formatMod($stat[$key]) }})</div>
            </div>
        @endforeach
    </div>

    <hr class="border-red-300 dark:border-red-800" />

    {{-- Proficiencies --}}
    @if (!empty($stat['proficiencies']))
        <div class="text-sm">
            <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Proficiencies') }}</span>
            <span class="text-zinc-600 dark:text-zinc-400">
                @foreach ($stat['proficiencies'] as $prof)
                    {{ is_array($prof) ? ($prof['proficiency']['name'] ?? $prof['name'] ?? '') . ' ' . ($prof['value'] ?? '') : $prof }}{{ !$loop->last ? ', ' : '' }}
                @endforeach
            </span>
        </div>
    @endif

    {{-- Damage/Condition Info --}}
    @if (!empty($stat['damage_vulnerabilities']))
        <div class="text-sm">
            <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Damage Vulnerabilities') }}</span>
            <span class="text-zinc-600 dark:text-zinc-400">{{ is_array($stat['damage_vulnerabilities']) ? implode(', ', $stat['damage_vulnerabilities']) : $stat['damage_vulnerabilities'] }}</span>
        </div>
    @endif

    @if (!empty($stat['damage_resistances']))
        <div class="text-sm">
            <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Damage Resistances') }}</span>
            <span class="text-zinc-600 dark:text-zinc-400">{{ is_array($stat['damage_resistances']) ? implode(', ', $stat['damage_resistances']) : $stat['damage_resistances'] }}</span>
        </div>
    @endif

    @if (!empty($stat['damage_immunities']))
        <div class="text-sm">
            <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Damage Immunities') }}</span>
            <span class="text-zinc-600 dark:text-zinc-400">{{ is_array($stat['damage_immunities']) ? implode(', ', $stat['damage_immunities']) : $stat['damage_immunities'] }}</span>
        </div>
    @endif

    @if (!empty($stat['condition_immunities']))
        <div class="text-sm">
            <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Condition Immunities') }}</span>
            <span class="text-zinc-600 dark:text-zinc-400">
                @foreach ($stat['condition_immunities'] as $ci)
                    {{ is_array($ci) ? ($ci['name'] ?? '') : $ci }}{{ !$loop->last ? ', ' : '' }}
                @endforeach
            </span>
        </div>
    @endif

    {{-- Senses & Languages --}}
    @if (!empty($stat['senses']))
        <div class="text-sm">
            <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Senses') }}</span>
            <span class="text-zinc-600 dark:text-zinc-400">
                @if (is_array($stat['senses']))
                    {{ implode(', ', array_map(fn($k, $v) => $k . ' ' . $v, array_keys($stat['senses']), $stat['senses'])) }}
                @else
                    {{ $stat['senses'] }}
                @endif
            </span>
        </div>
    @endif

    @if (!empty($stat['languages']))
        <div class="text-sm">
            <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Languages') }}</span>
            <span class="text-zinc-600 dark:text-zinc-400">{{ $stat['languages'] }}</span>
        </div>
    @endif

    <div class="text-sm">
        <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ __('Challenge') }}</span>
        <span class="text-zinc-600 dark:text-zinc-400">{{ $stat['challenge_rating'] }} ({{ number_format($stat['xp'] ?? 0) }} XP)</span>
    </div>

    <hr class="border-red-300 dark:border-red-800" />

    {{-- Special Abilities --}}
    @if (!empty($stat['special_abilities']))
        <div class="space-y-2">
            @foreach ($stat['special_abilities'] as $ability)
                <div class="text-sm">
                    <span class="font-semibold italic text-zinc-700 dark:text-zinc-200">{{ $ability['name'] ?? '' }}.</span>
                    <span class="text-zinc-600 dark:text-zinc-400">{{ $ability['desc'] ?? '' }}</span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Actions --}}
    @if (!empty($stat['actions']))
        <div>
            <div class="mb-1 text-base font-bold text-red-700 dark:text-red-400">{{ __('Actions') }}</div>
            <div class="space-y-2">
                @foreach ($stat['actions'] as $action)
                    <div class="text-sm">
                        <span class="font-semibold italic text-zinc-700 dark:text-zinc-200">{{ $action['name'] ?? '' }}.</span>
                        <span class="text-zinc-600 dark:text-zinc-400">{{ $action['desc'] ?? '' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Reactions --}}
    @if (!empty($stat['reactions']))
        <div>
            <div class="mb-1 text-base font-bold text-red-700 dark:text-red-400">{{ __('Reactions') }}</div>
            <div class="space-y-2">
                @foreach ($stat['reactions'] as $reaction)
                    <div class="text-sm">
                        <span class="font-semibold italic text-zinc-700 dark:text-zinc-200">{{ $reaction['name'] ?? '' }}.</span>
                        <span class="text-zinc-600 dark:text-zinc-400">{{ $reaction['desc'] ?? '' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Legendary Actions --}}
    @if (!empty($stat['legendary_actions']))
        <div>
            <div class="mb-1 text-base font-bold text-red-700 dark:text-red-400">{{ __('Legendary Actions') }}</div>
            <div class="space-y-2">
                @foreach ($stat['legendary_actions'] as $la)
                    <div class="text-sm">
                        <span class="font-semibold italic text-zinc-700 dark:text-zinc-200">{{ $la['name'] ?? '' }}.</span>
                        <span class="text-zinc-600 dark:text-zinc-400">{{ $la['desc'] ?? '' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
