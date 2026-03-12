<?php

namespace App\Livewire\Forms;

use App\Models\Campaign;
use App\Models\Location;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LocationForm extends Form
{
    #[Validate(['required', 'string', 'max:255'])]
    public string $name = '';

    #[Validate(['nullable', 'string', 'max:5000'])]
    public string $description = '';

    #[Validate(['nullable', 'string', 'max:255'])]
    public string $region = '';

    #[Validate(['nullable', 'exists:locations,id'])]
    public ?int $parentLocationId = null;

    public function setLocation(Location $location): void
    {
        $this->name = $location->name;
        $this->description = $location->description ?? '';
        $this->region = $location->region ?? '';
        $this->parentLocationId = $location->parent_location_id;
    }

    public function store(Campaign $campaign): Location
    {
        $this->validate();

        /** @var Location $location */
        $location = $campaign->locations()->create([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'region' => $this->region ?: null,
            'parent_location_id' => $this->parentLocationId,
        ]);

        $this->resetForm();

        return $location;
    }

    public function update(Location $location): void
    {
        $this->validate();

        $location->update([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'region' => $this->region ?: null,
            'parent_location_id' => $this->parentLocationId,
        ]);

        $this->resetForm();
    }

    public function destroy(Location $location): void
    {
        $location->delete();
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'description', 'region', 'parentLocationId']);
    }
}
