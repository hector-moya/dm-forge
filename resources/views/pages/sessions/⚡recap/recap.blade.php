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

    {{-- Streaming preview --}}
    <div wire:loading wire:target="generateRecap" class="rounded-xl border border-indigo-200 bg-indigo-50 p-6 dark:border-indigo-700 dark:bg-indigo-900/20">
        <div class="mb-3 flex items-center gap-3">
            <div class="size-5 animate-spin rounded-full border-2 border-indigo-200 border-t-indigo-600"></div>
            <flux:heading size="lg" class="text-indigo-600 dark:text-indigo-400">{{ __('Writing recap...') }}</flux:heading>
        </div>
        <div class="prose prose-zinc dark:prose-invert max-w-none whitespace-pre-wrap text-sm" wire:stream="streamedRecap"></div>
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

    {{-- Session Notes --}}
    @if ($logs->isNotEmpty())
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading size="lg" class="mb-4">{{ __('Session Notes') }}</flux:heading>
            <div class="space-y-1">
                @foreach ($logs as $log)
                    <div class="group rounded px-2 py-1.5 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                        <div class="flex items-center gap-2">
                            <span class="shrink-0 text-xs text-zinc-400 dark:text-zinc-500">{{ $log->logged_at?->format('H:i') }}</span>
                            @php
                                $logColor = match($log->type) {
                                    'combat' => 'text-red-500',
                                    'decision' => 'text-amber-500',
                                    'narrative' => 'text-blue-500',
                                    default => 'text-zinc-400',
                                };
                            @endphp
                            <span class="shrink-0 text-xs font-semibold uppercase {{ $logColor }}">{{ $log->type }}</span>
                            @if (! empty($log->character_ids))
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($characters->whereIn('id', $log->character_ids) as $character)
                                        <flux:badge color="blue" size="xs">{{ \Illuminate\Support\Str::before($character->name, ' ') ?: $character->name }}</flux:badge>
                                    @endforeach
                                </div>
                            @endif
                            <div class="ml-auto shrink-0">
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button icon="ellipsis-horizontal" variant="ghost" size="xs" class="opacity-0 group-hover:opacity-100" />
                                    <flux:menu>
                                        <flux:menu.item icon="eye" wire:click="openViewLog({{ $log->id }})">{{ __('View') }}</flux:menu.item>
                                        <flux:menu.item icon="pencil" wire:click="openEditLog({{ $log->id }})">{{ __('Edit') }}</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" wire:click="deleteLog({{ $log->id }})">{{ __('Delete') }}</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </div>
                        <div class="mt-0.5 flex flex-wrap items-baseline gap-x-2 pl-10">
                            <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $log->entry }}</p>
                            @if ($log->scene_id)
                                <span class="text-xs text-zinc-400 dark:text-zinc-500">· {{ $allScenes->firstWhere('id', $log->scene_id)?->title }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if (! $session->generated_narrative && $logs->isEmpty())
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

    {{-- View Log Modal --}}
    <flux:modal wire:model="showViewLogModal" variant="dialog" class="max-w-lg">
        @php
            $viewingLog = $viewingLogId ? $logs->firstWhere('id', $viewingLogId) : null;
        @endphp
        @if ($viewingLog)
            <div class="space-y-4">
                <flux:heading size="lg">{{ __('View Note') }}</flux:heading>

                <div>
                    <span class="mb-1 block text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Type') }}</span>
                    @php
                        $viewColor = match($viewingLog->type) {
                            'combat' => 'red',
                            'decision' => 'amber',
                            'narrative' => 'blue',
                            default => 'zinc',
                        };
                    @endphp
                    <flux:badge :color="$viewColor" size="sm">{{ ucfirst($viewingLog->type) }}</flux:badge>
                </div>

                @if ($viewingLog->scene_id)
                    <div>
                        <span class="mb-1 block text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Scene') }}</span>
                        <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $allScenes->firstWhere('id', $viewingLog->scene_id)?->title }}</p>
                    </div>
                @endif

                @if (! empty($viewingLog->character_ids))
                    <div>
                        <span class="mb-1 block text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Characters involved') }}</span>
                        <div class="flex flex-wrap gap-1">
                            @foreach ($characters->whereIn('id', $viewingLog->character_ids) as $character)
                                <flux:badge color="blue" size="sm">{{ $character->name }}</flux:badge>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <span class="mb-1 block text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Description') }}</span>
                    <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $viewingLog->entry }}</p>
                </div>

                <div class="flex justify-end">
                    <flux:button variant="subtle" wire:click="$set('showViewLogModal', false)">
                        {{ __('Close') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>

    {{-- Edit Log Modal --}}
    <flux:modal wire:model="showEditLogModal" variant="dialog" class="max-w-lg">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Edit Note') }}</flux:heading>

            <flux:select wire:model="editLogType" label="{{ __('Note type') }}">
                <flux:select.option value="combat">{{ __('Combat') }}</flux:select.option>
                <flux:select.option value="narrative">{{ __('Narrative') }}</flux:select.option>
                <flux:select.option value="decision">{{ __('Decision') }}</flux:select.option>
                <flux:select.option value="note">{{ __('Note') }}</flux:select.option>
            </flux:select>

            <div>
                <div class="mb-1 flex items-center justify-between">
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Characters involved') }}</span>
                    <flux:button variant="subtle" size="xs" wire:click="toggleAllEditLogCharacters">
                        {{ count($editLogCharacterIds) === $characters->count() ? __('Deselect All') : __('Select All') }}
                    </flux:button>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($characters as $character)
                        <label class="flex cursor-pointer items-center gap-1.5 rounded-lg border border-zinc-200 px-2 py-1 text-sm dark:border-zinc-600
                            {{ in_array($character->id, $editLogCharacterIds) ? 'bg-blue-50 border-blue-300 dark:bg-blue-900/30 dark:border-blue-600' : '' }}">
                            <input type="checkbox" value="{{ $character->id }}" wire:model="editLogCharacterIds"
                                   class="rounded border-zinc-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-700" />
                            {{ $character->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <flux:textarea
                wire:model="editLogEntry"
                label="{{ __('Description') }}"
                rows="3"
                required
            />

            <div class="flex justify-end gap-2">
                <flux:button variant="subtle" wire:click="$set('showEditLogModal', false)">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" wire:click="saveEditLog">
                    {{ __('Save Changes') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
