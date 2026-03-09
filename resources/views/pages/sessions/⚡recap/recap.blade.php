<div class="mx-auto flex w-full max-w-4xl flex-col gap-6"
     x-on:recap-error.window="Flux.toast({ text: 'Recap generation failed: ' + $event.detail.message, variant: 'danger' })">
    {{-- Header --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <flux:button variant="subtle" href="{{ route('campaigns.sessions', $session->campaign) }}" wire:navigate icon="arrow-left" size="sm">
                {{ __('Sessions') }}
            </flux:button>
            <div>
                <flux:heading size="xl">{{ __('Session Recap') }}</flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                    #{{ $session->session_number }} &mdash; {{ $session->title }}
                </flux:text>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if ($session->generated_narrative)
                <flux:button variant="subtle" size="sm" wire:click="generateRecap" wire:loading.attr="disabled" wire:target="generateRecap" icon="arrow-path">
                    <span wire:loading.remove wire:target="generateRecap">{{ __('Regenerate') }}</span>
                    <span wire:loading wire:target="generateRecap">{{ __('Generating...') }}</span>
                </flux:button>
                <flux:button variant="subtle" size="sm" wire:click="clearRecap" wire:confirm="{{ __('Clear the generated recap?') }}" icon="trash">
                    {{ __('Clear') }}
                </flux:button>
            @else
                <flux:button variant="primary" wire:click="generateRecap" wire:loading.attr="disabled" wire:target="generateRecap" icon="sparkles">
                    <span wire:loading.remove wire:target="generateRecap">{{ __('Generate Recap') }}</span>
                    <span wire:loading wire:target="generateRecap">{{ __('Generating...') }}</span>
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Loading indicator --}}
    <div wire:loading wire:target="generateRecap" class="rounded-xl border border-indigo-200 bg-indigo-50 p-6 text-center dark:border-indigo-700 dark:bg-indigo-900/20">
        <div class="mx-auto mb-3 size-8 animate-spin rounded-full border-4 border-indigo-200 border-t-indigo-600"></div>
        <flux:text class="text-indigo-600 dark:text-indigo-400">{{ __('AI is writing your session recap...') }}</flux:text>
    </div>

    @if ($session->generated_narrative)
        {{-- Narrative Recap --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">{{ __('Narrative Recap') }}</flux:heading>
            <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                {!! nl2br(e($session->generated_narrative)) !!}
            </div>
        </div>

        {{-- Key Events --}}
        @if ($session->generated_bullets)
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg" class="mb-4">{{ __('Key Events') }}</flux:heading>
                <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                    {!! nl2br(e($session->generated_bullets)) !!}
                </div>
            </div>
        @endif

        {{-- Plot Hooks --}}
        @if ($session->generated_hooks)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-700 dark:bg-amber-900/20">
                <flux:heading size="lg" class="mb-4">{{ __('Plot Hooks') }}</flux:heading>
                <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                    {!! nl2br(e($session->generated_hooks)) !!}
                </div>
            </div>
        @endif

        {{-- World State Changes --}}
        @if ($session->generated_world_state)
            <div class="rounded-xl border border-green-200 bg-green-50 p-6 dark:border-green-700 dark:bg-green-900/20">
                <flux:heading size="lg" class="mb-4">{{ __('World State Changes') }}</flux:heading>
                <div class="prose prose-zinc dark:prose-invert max-w-none text-sm">
                    {!! nl2br(e($session->generated_world_state)) !!}
                </div>
            </div>
        @endif
    @endif

    {{-- Raw Session Log --}}
    @if ($logs->isNotEmpty())
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">{{ __('Session Log') }}</flux:heading>
            <div class="max-h-96 space-y-1 overflow-y-auto">
                @foreach ($logs as $log)
                    <div class="flex items-start gap-3 rounded px-2 py-1 text-sm">
                        <span class="shrink-0 text-xs text-zinc-400 dark:text-zinc-500">{{ $log->logged_at?->format('H:i:s') }}</span>
                        @php
                            $logColor = match($log->type) {
                                'combat' => 'text-red-500',
                                'decision' => 'text-amber-500',
                                'narrative' => 'text-blue-500',
                                default => 'text-zinc-400',
                            };
                        @endphp
                        <span class="w-16 shrink-0 text-xs font-semibold uppercase {{ $logColor }}">{{ $log->type }}</span>
                        <span class="text-zinc-700 dark:text-zinc-300">{{ $log->entry }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if (!$session->generated_narrative && $logs->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-300 p-12 dark:border-zinc-600">
            <flux:icon name="book-open" class="mx-auto mb-4 size-12 text-zinc-400 dark:text-zinc-500" />
            <flux:heading size="lg" class="mb-2">{{ __('No recap yet') }}</flux:heading>
            <flux:text class="mb-6 text-zinc-500 dark:text-zinc-400">
                {{ __('Generate an AI-powered recap of this session.') }}
            </flux:text>
            <flux:button variant="primary" wire:click="generateRecap" wire:loading.attr="disabled" wire:target="generateRecap" icon="sparkles">
                {{ __('Generate Recap') }}
            </flux:button>
        </div>
    @endif
</div>
