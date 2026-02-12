<?php

use App\Models\BranchOption;
use Livewire\Component;

new class extends Component
{
    public int $branchId;

    public bool $showForm = true;

    public string $type = 'immediate';

    public string $description = '';

    public function save(): void
    {
        $this->validate([
            'type' => ['required', 'in:immediate,delayed,meta'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        $branch = BranchOption::query()->findOrFail($this->branchId);
        $branch->consequences()->create([
            'type' => $this->type,
            'description' => $this->description,
        ]);

        \Flux::toast(__('Consequence added successfully'));
        $this->showForm = false;
        $this->dispatch('$refresh');
    }
};
