<div class="space-y-6">
    <div>
        <flux:heading size="lg">{{ $worldRule->name }}</flux:heading>
    </div>

    <flux:separator />

    @if ($worldRule->description)
        <div>
            <flux:heading size="sm" class="mb-1">{{ __('Description') }}</flux:heading>
            <flux:text class="text-sm">{{ $worldRule->description }}</flux:text>
        </div>
    @endif

    @if ($worldRule->dm_notes)
        <div>
            <flux:heading size="sm" class="mb-1">{{ __('DM Notes') }}</flux:heading>
            <flux:text class="text-sm">{{ $worldRule->dm_notes }}</flux:text>
        </div>
    @endif
</div>
