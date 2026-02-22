<?php

use App\Ai\Agents\LocationGenerator;
use App\Livewire\Forms\LocationForm;
use App\Models\Campaign;
use App\Models\Location;
use App\Services\EntityImageGenerator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public LocationForm $form;

    public Campaign $campaign;

    public string $search = '';

    public string $regionFilter = '';

    // Detail flyout
    public ?int $viewingLocationId = null;

    // Inline form
    public bool $showForm = false;

    public ?int $editingLocationId = null;

    // Generator
    public bool $showGenerateModal = false;

    public string $generateContext = '';

    public bool $generating = false;

    public bool $generateImageOnCreate = false;

    public bool $pendingImageGeneration = false;

    public function mount(Campaign $campaign): void
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        $this->campaign = $campaign;
    }

    public function viewLocation(int $id): void
    {
        $this->viewingLocationId = $id;
        $this->modal('view-location')->show();
    }

    #[Computed]
    public function viewingLocation(): ?Location
    {
        if (! $this->viewingLocationId) {
            return null;
        }

        return $this->campaign->locations()
            ->with(['children', 'parent', 'npcs'])
            ->find($this->viewingLocationId);
    }

    #[Computed]
    public function regions(): array
    {
        return $this->campaign->locations()
            ->distinct()
            ->whereNotNull('region')
            ->where('region', '!=', '')
            ->pluck('region')
            ->sort()
            ->values()
            ->toArray();
    }

    // ── CRUD ──────────────────────────────────────────────────────────

    public function openForm(?int $locationId = null): void
    {
        $this->resetForm();
        $this->showForm = true;

        if ($locationId) {
            $location = $this->campaign->locations()->findOrFail($locationId);
            $this->editingLocationId = $location->id;
            $this->form->setLocation($location);
        }
    }

    public function save(): void
    {
        if ($this->editingLocationId) {
            $location = $this->campaign->locations()->findOrFail($this->editingLocationId);
            $this->form->update($location);
            \Flux::toast(__('Location updated.'));
        } else {
            $location = $this->form->store($this->campaign);
            \Flux::toast(__('Location created.'));

            if ($this->pendingImageGeneration) {
                try {
                    app(EntityImageGenerator::class)->generate(
                        $location, 'location', null,
                        fn (string $status) => $this->stream(to: 'imageStatus', content: $status, replace: true),
                    );
                    \Flux::toast(__('Image generated!'));
                } catch (\Throwable) {
                    \Flux::toast(__('Location saved, but image generation failed.'));
                }
            }
        }

        $this->resetForm();
    }

    public function delete(int $locationId): void
    {
        $this->campaign->locations()->findOrFail($locationId)->delete();

        if ($this->viewingLocationId === $locationId) {
            $this->viewingLocationId = null;
        }

        \Flux::toast(__('Location deleted.'));
    }

    private function resetForm(): void
    {
        $this->showForm = false;
        $this->editingLocationId = null;
        $this->pendingImageGeneration = false;
        $this->form->resetForm();
        $this->resetValidation();
    }

    // ── Generator ─────────────────────────────────────────────────────

    public function openGenerateModal(): void
    {
        $this->showGenerateModal = true;
        $this->generateContext = '';
        $this->generating = false;
    }

    public function generate(): void
    {
        $this->generating = true;

        try {
            $generator = new LocationGenerator($this->campaign);
            $prompt = 'Generate a unique location for this campaign.';
            if ($this->generateContext) {
                $prompt .= " Context: {$this->generateContext}";
            }

            $response = $generator->prompt($prompt);

            $this->showGenerateModal = false;

            $this->resetForm();
            $this->showForm = true;
            $this->form->name = $response['name'] ?? '';
            $this->form->description = $response['description'] ?? '';
            $this->form->region = $response['region'] ?? '';

            if (! empty($response['history'])) {
                $this->form->description .= "\n\nHistory: ".$response['history'];
            }

            $this->pendingImageGeneration = $this->generateImageOnCreate;

            \Flux::toast(__('Location generated! Review and save below.'));
        } catch (\Throwable $e) {
            \Flux::toast(__('Generation failed: ').$e->getMessage());
        }

        $this->generating = false;
    }

    // ── Image Generation ──────────────────────────────────────────────

    public function generateImage(int $locationId): void
    {
        $location = $this->campaign->locations()->findOrFail($locationId);

        try {
            $path = app(EntityImageGenerator::class)->generate(
                $location, 'location', null,
                fn (string $status) => $this->stream(to: 'imageStatus', content: $status, replace: true),
            );

            if ($path) {
                \Flux::toast(__('Image generated!'));
            } else {
                \Flux::toast(__('Image generation failed.'));
            }
        } catch (\Throwable $e) {
            \Flux::toast(__('Image generation failed: ').$e->getMessage());
        }
    }

    // ── Render ─────────────────────────────────────────────────────────

    public function getLocations(): Collection
    {
        $query = $this->campaign->locations()->with('parent')->withCount(['children', 'npcs']);

        if ($this->search !== '') {
            $query->where('name', 'like', "%{$this->search}%");
        }

        if ($this->regionFilter !== '') {
            $query->where('region', $this->regionFilter);
        }

        return $query->orderBy('name')->get();
    }

    public function render(): \Illuminate\View\View
    {
        $viewingLocation = $this->viewingLocation;

        return view('pages.campaigns.⚡location-manager.location-manager', [
            'locations' => $this->getLocations(),
            'allLocations' => $this->campaign->locations()->orderBy('name')->get(),
            'viewingLocation' => $viewingLocation,
            'regions' => $this->regions,
            'history' => $viewingLocation
                ? $viewingLocation->worldEvents()->with(['faction', 'gameSession'])->orderByDesc('occurred_at')->limit(20)->get()
                : collect(),
        ])->title(__('Locations').' — '.$this->campaign->name);
    }
};
