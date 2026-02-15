<?php

namespace App\Livewire\Campaigns;

use App\Ai\Agents\LocationGenerator;
use App\Models\Campaign;
use App\Models\Location;
use Flux;
use Illuminate\Support\Collection;
use Livewire\Component;

class LocationManager extends Component
{
    public Campaign $campaign;

    public string $search = '';

    public string $regionFilter = '';

    // Detail flyout
    public ?int $viewingLocationId = null;

    // Inline form
    public bool $showForm = false;

    public ?int $editingLocationId = null;

    public string $locationName = '';

    public string $locationDescription = '';

    public string $locationRegion = '';

    public ?int $locationParentId = null;

    // Generator
    public bool $showGenerateModal = false;

    public string $generateContext = '';

    public bool $generating = false;

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

    public function getViewingLocationProperty(): ?Location
    {
        if (! $this->viewingLocationId) {
            return null;
        }

        return $this->campaign->locations()
            ->with(['children', 'parent', 'npcs'])
            ->find($this->viewingLocationId);
    }

    // ── CRUD ──────────────────────────────────────────────────────────

    public function openForm(?int $locationId = null): void
    {
        $this->resetForm();
        $this->showForm = true;

        if ($locationId) {
            $location = $this->campaign->locations()->findOrFail($locationId);
            $this->editingLocationId = $location->id;
            $this->locationName = $location->name;
            $this->locationDescription = $location->description ?? '';
            $this->locationRegion = $location->region ?? '';
            $this->locationParentId = $location->parent_location_id;
        }
    }

    public function save(): void
    {
        $this->validate([
            'locationName' => ['required', 'string', 'max:255'],
            'locationDescription' => ['nullable', 'string', 'max:5000'],
            'locationRegion' => ['nullable', 'string', 'max:255'],
            'locationParentId' => ['nullable', 'exists:locations,id'],
        ]);

        $data = [
            'name' => $this->locationName,
            'description' => $this->locationDescription ?: null,
            'region' => $this->locationRegion ?: null,
            'parent_location_id' => $this->locationParentId,
        ];

        if ($this->editingLocationId) {
            $this->campaign->locations()->findOrFail($this->editingLocationId)->update($data);
            Flux::toast(__('Location updated.'));
        } else {
            $this->campaign->locations()->create($data);
            Flux::toast(__('Location created.'));
        }

        $this->resetForm();
    }

    public function delete(int $locationId): void
    {
        $this->campaign->locations()->findOrFail($locationId)->delete();

        if ($this->viewingLocationId === $locationId) {
            $this->viewingLocationId = null;
        }

        Flux::toast(__('Location deleted.'));
    }

    private function resetForm(): void
    {
        $this->showForm = false;
        $this->editingLocationId = null;
        $this->locationName = '';
        $this->locationDescription = '';
        $this->locationRegion = '';
        $this->locationParentId = null;
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
            $this->locationName = $response['name'] ?? '';
            $this->locationDescription = $response['description'] ?? '';
            $this->locationRegion = $response['region'] ?? '';

            if (! empty($response['history'])) {
                $this->locationDescription .= "\n\nHistory: ".$response['history'];
            }

            Flux::toast(__('Location generated! Review and save below.'));
        } catch (\Throwable $e) {
            Flux::toast(__('Generation failed: ').$e->getMessage());
        }

        $this->generating = false;
    }

    // ── Render ─────────────────────────────────────────────────────────

    public function getRegionsProperty(): array
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

    public function render()
    {
        $viewingLocation = $this->viewingLocation;

        return view('livewire.campaigns.location-manager', [
            'locations' => $this->getLocations(),
            'allLocations' => $this->campaign->locations()->orderBy('name')->get(),
            'viewingLocation' => $viewingLocation,
            'regions' => $this->regions,
            'history' => $viewingLocation
                ? $viewingLocation->worldEvents()->with(['faction', 'gameSession'])->orderByDesc('occurred_at')->limit(20)->get()
                : collect(),
        ])->title(__('Locations').' — '.$this->campaign->name);
    }
}
