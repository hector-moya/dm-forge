<?php

use App\Livewire\Forms\SpecialMechanicForm;
use App\Models\Campaign;
use App\Models\SpecialMechanic;
use App\Models\SpecialMechanicRule;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public SpecialMechanicForm $form;

    public Campaign $campaign;

    public ?int $selectedMechanicId = null;

    public string $search = '';

    // Rules management (for existing mechanics)
    public ?int $editingMechanicForRulesId = null;

    public bool $showRuleForm = false;

    public ?int $editingRuleId = null;

    public string $ruleName = '';

    public string $ruleDescription = '';

    public string $ruleNotes = '';

    public function mount(Campaign $campaign): void
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        $this->campaign = $campaign;
    }

    #[Computed]
    public function campaignSpecialMechanics(): Collection
    {
        return $this->campaign->specialMechanics()
            ->withCount('rules')
            ->where('name', 'like', "%{$this->search}%")
            ->get();
    }

    #[Computed]
    public function editingMechanicRules(): Collection
    {
        if (! $this->editingMechanicForRulesId) {
            return collect();
        }

        return SpecialMechanic::findOrFail($this->editingMechanicForRulesId)
            ->rules()
            ->orderBy('name')
            ->get();
    }

    public function setMechanicId(?int $id): void
    {
        $this->selectedMechanicId = $id;

        if ($this->selectedMechanicId) {
            $this->form->setSpecialMechanic(SpecialMechanic::findOrFail($this->selectedMechanicId));
        }
    }

    public function save(): void
    {
        if ($this->selectedMechanicId) {
            $this->form->update(SpecialMechanic::findOrFail($this->selectedMechanicId));
        } else {
            $this->form->store($this->campaign);
        }

        $this->resetSelectedMechanicId();
    }

    public function resetSelectedMechanicId(): void
    {
        $this->modal('create-mechanic')->close();

        $this->selectedMechanicId = null;

        $this->form->resetForm();
    }

    public function openViewMechanicModal(int $id): void
    {
        $this->selectedMechanicId = $id;
        $this->modal("view-mechanic-{$id}")->show();
    }

    // ── Rules ──────────────────────────────────────────────────────────

    public function openRulesPanel(int $mechanicId): void
    {
        $this->editingMechanicForRulesId = $mechanicId;
        $this->resetRuleForm();
    }

    public function closeRulesPanel(): void
    {
        $this->editingMechanicForRulesId = null;
        $this->resetRuleForm();
    }

    public function openRuleForm(?int $ruleId = null): void
    {
        $this->resetRuleForm();
        $this->showRuleForm = true;

        if ($ruleId) {
            $rule = SpecialMechanicRule::findOrFail($ruleId);
            $this->editingRuleId = $rule->id;
            $this->ruleName = $rule->name;
            $this->ruleDescription = $rule->description ?? '';
            $this->ruleNotes = $rule->notes ?? '';
        }
    }

    public function saveRule(): void
    {
        $this->validate([
            'ruleName' => ['required', 'string', 'max:255'],
            'ruleDescription' => ['nullable', 'string', 'max:5000'],
            'ruleNotes' => ['nullable', 'string', 'max:5000'],
        ]);

        $data = [
            'name' => $this->ruleName,
            'description' => $this->ruleDescription ?: null,
            'notes' => $this->ruleNotes ?: null,
        ];

        $mechanic = SpecialMechanic::findOrFail($this->editingMechanicForRulesId);

        if ($this->editingRuleId) {
            $mechanic->rules()->findOrFail($this->editingRuleId)->update($data);
            \Flux::toast(__('Rule updated.'));
        } else {
            $mechanic->rules()->create($data);
            \Flux::toast(__('Rule added.'));
        }

        $this->resetRuleForm();
    }

    public function deleteRule(int $ruleId): void
    {
        SpecialMechanicRule::findOrFail($ruleId)->delete();
        \Flux::toast(__('Rule deleted.'));
    }

    private function resetRuleForm(): void
    {
        $this->showRuleForm = false;
        $this->editingRuleId = null;
        $this->ruleName = '';
        $this->ruleDescription = '';
        $this->ruleNotes = '';
        $this->resetValidation();
    }

    public function render(): \Illuminate\View\View
    {
        return view('pages.campaigns.⚡special-mechanics-manager.special-mechanics-manager')
            ->title(__('Special Mechanics').' — '.$this->campaign->name);
    }
};
