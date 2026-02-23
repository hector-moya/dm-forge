<?php

use App\Models\Campaign;
use App\Models\Lore;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use App\Livewire\Forms\LoreForm;
use Livewire\Component;

new class extends Component
{

    public LoreForm $form;
    public Campaign $campaign;
    public ?Collection $lores = null;
    public ?int $selectedLoreId = null;
    public int $editingLoreId = 0;

    public string $search = '';

    public function mount(Campaign $campaign): void
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        $this->campaign = $campaign;
    }

    #[Computed]
    public function campaignLore(): Collection
    {
        return $this->campaign->lores()->where('name', 'like', "%{$this->search}%")->get();
    }

    public function setLoreId(?int $id): void
    {
        $this->selectedLoreId = $id;

        if ($this->selectedLoreId) {
            $this->form->setLore(Lore::findOrFail($this->selectedLoreId));
        }
    }

    public function save(): void
    {
        if ($this->selectedLoreId) {
            $lore = Lore::findOrFail($this->selectedLoreId);
            $this->form->update($lore);
        } else {
            $this->form->store($this->campaign);
        }

        $this->resetSelectedLoreId();
    }

    public function openCreateLoreModal(): void
    {
        $this->selectedLoreId = null;
        $this->form->resetForm();
        $this->modal('create-lore')->show();
    }

    public function resetSelectedLoreId(): void
    {
        $this->modal('create-lore')->close();

        $this->selectedLoreId = null;

        $this->form->resetForm();
    }

    public function openViewLoreModal(int $id): void
    {
        $this->selectedLoreId = $id;
        $this->modal("view-lore-{$id}")->show();
    }

    public function render(): mixed
    {
        return $this->view()->title(__('Lore Manager'));
    }

};
