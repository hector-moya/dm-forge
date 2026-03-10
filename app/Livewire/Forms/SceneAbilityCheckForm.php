<?php

namespace App\Livewire\Forms;

use App\Models\Scene;
use App\Models\SceneAbilityCheck;
use Livewire\Attributes\Validate;
use Livewire\Form;

class SceneAbilityCheckForm extends Form
{
    #[Validate(['required', 'string'])]
    public string $skill = '';

    #[Validate(['nullable', 'string', 'max:255'])]
    public string $subject = '';

    #[Validate(['required', 'integer', 'min:1', 'max:30'])]
    public int $dc = 10;

    #[Validate(['nullable', 'integer', 'min:1', 'max:30'])]
    public ?int $dcSuper = null;

    #[Validate(['required', 'string', 'max:2000'])]
    public string $failureText = '';

    #[Validate(['required', 'string', 'max:2000'])]
    public string $successText = '';

    #[Validate(['nullable', 'string', 'max:2000'])]
    public string $superSuccessText = '';

    public function setCheck(SceneAbilityCheck $check): void
    {
        $this->skill = $check->skill->value;
        $this->subject = $check->subject ?? '';
        $this->dc = $check->dc;
        $this->dcSuper = $check->dc_super;
        $this->failureText = $check->failure_text;
        $this->successText = $check->success_text;
        $this->superSuccessText = $check->super_success_text ?? '';
    }

    public function store(Scene $scene): SceneAbilityCheck
    {
        $this->validate();

        $sortOrder = $scene->abilityChecks()->max('sort_order') + 1;

        return $scene->abilityChecks()->create($this->buildData($sortOrder));
    }

    public function update(SceneAbilityCheck $check): void
    {
        $this->validate();

        $check->update($this->buildData($check->sort_order));
    }

    public function destroy(SceneAbilityCheck $check): void
    {
        $check->delete();
    }

    public function resetForm(): void
    {
        $this->skill = '';
        $this->subject = '';
        $this->dc = 10;
        $this->dcSuper = null;
        $this->failureText = '';
        $this->successText = '';
        $this->superSuccessText = '';
        $this->resetValidation();
    }

    private function buildData(int $sortOrder): array
    {
        return [
            'skill' => $this->skill,
            'subject' => $this->subject ?: null,
            'dc' => $this->dc,
            'dc_super' => $this->dcSuper ?: null,
            'failure_text' => $this->failureText,
            'success_text' => $this->successText,
            'super_success_text' => $this->superSuccessText ?: null,
            'sort_order' => $sortOrder,
        ];
    }
}
