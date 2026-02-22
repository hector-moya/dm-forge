<flux:card class="space-y-4">
    @if ($campaign->premise)
        <div class="flex justify-between items-center">
            <flux:heading size="lg">{{ __('Details') }}</flux:heading>
            <flux:button variant="primary" size="xs" href="{{ route('campaigns.edit', $campaign) }}" wire:navigate icon="pencil">
                {{ __('Edit') }}
            </flux:button>

        </div>
        <div>
            <flux:heading>{{ __('Title') }}</flux:heading>
            <flux:text>{{ $campaign->name }}</flux:text>
        </div>
        <div>
            <flux:heading>{{ __('Theme & Tone') }}</flux:heading>
            <flux:text>{{ $campaign->theme_tone }}</flux:text>
        </div>
        <div>
            <flux:heading>{{ __('Premise') }}</flux:heading>
            <flux:text>{{ $campaign->premise }}</flux:text>
        </div>
    @else
        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No premise provided.') }}</flux:text>
    @endif
</flux:card>
