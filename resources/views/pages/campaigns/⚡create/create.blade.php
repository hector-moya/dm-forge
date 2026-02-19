<div class="mx-auto flex w-full max-w-3xl flex-col gap-6">
    <div class="flex items-center gap-4">
        <flux:button variant="subtle" href="{{ route('dashboard') }}" wire:navigate icon="arrow-left" size="sm">
            {{ __('Back') }}
        </flux:button>
        <flux:heading size="xl">{{ __('Create Campaign') }}</flux:heading>
    </div>

    <form wire:submit="save" class="flex flex-col gap-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">{{ __('Campaign Details') }}</flux:heading>

            <div class="flex flex-col gap-4">
                <flux:input
                    wire:model="name"
                    label="{{ __('Campaign Name') }}"
                    placeholder="{{ __('Enter campaign name...') }}"
                    required
                />

                <flux:textarea
                    wire:model="premise"
                    label="{{ __('Premise') }}"
                    placeholder="{{ __('What is the central premise or hook of this campaign?') }}"
                    rows="3"
                />

                <flux:textarea
                    wire:model="lore"
                    label="{{ __('Lore') }}"
                    placeholder="{{ __('Background lore, history, and world-building details...') }}"
                    rows="5"
                />

                <flux:input
                    wire:model="theme_tone"
                    label="{{ __('Theme & Tone') }}"
                    placeholder="{{ __('e.g., Dark fantasy, Lighthearted adventure, Political intrigue...') }}"
                />

                <flux:textarea
                    wire:model="world_rules"
                    label="{{ __('World Rules') }}"
                    placeholder="{{ __('Special rules, house rules, or world-specific mechanics...') }}"
                    rows="4"
                />

                <flux:textarea
                    wire:model="special_mechanics"
                    label="{{ __('Special Mechanics') }}"
                    placeholder="{{ __('Any special mechanics as JSON or plain text...') }}"
                    rows="3"
                    description="{{ __('Enter as JSON array or plain text. Plain text will be stored as a single-item array.') }}"
                />
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <flux:button variant="subtle" href="{{ route('dashboard') }}" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary">
                {{ __('Create Campaign') }}
            </flux:button>
        </div>
    </form>
</div>
