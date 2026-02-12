<?php

use App\Models\Scene;
use Livewire\Component;

new class extends Component
{
    public Scene $scene;

    public int $sessionId;

    public bool $showForm = false;

    public ?int $editingSceneId = null;

    public string $title = '';

    public string $description = '';

    public string $notes = '';

    public function openForm(?int $sceneId = null): void
    {
        $this->showForm = true;
        $this->editingSceneId = $sceneId;

        if ($sceneId) {
            $scene = Scene::query()->findOrFail($sceneId);
            $this->title = $scene->title;
            $this->description = $scene->description ?? '';
            $this->notes = $scene->notes ?? '';
        } else {
            $this->resetForm();
        }
    }

    public function save(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $data = [
            'title' => $this->title,
            'description' => $this->description ?: null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingSceneId) {
            Scene::query()->findOrFail($this->editingSceneId)->update($data);
            \Flux::toast(__('Scene updated successfully'));
        } else {
            $maxSort = Scene::query()->where('game_session_id', $this->sessionId)->max('sort_order') ?? 0;
            Scene::query()->create(array_merge($data, [
                'game_session_id' => $this->sessionId,
                'sort_order' => $maxSort + 1,
            ]));
            \Flux::toast(__('Scene created successfully'));
        }

        $this->resetForm();
        $this->dispatch('$refresh');
    }

    public function delete(int $sceneId): void
    {
        Scene::query()->findOrFail($sceneId)->delete();
        \Flux::toast(__('Scene deleted successfully'));
        $this->dispatch('$refresh');
    }

    private function resetForm(): void
    {
        $this->showForm = false;
        $this->editingSceneId = null;
        $this->title = '';
        $this->description = '';
        $this->notes = '';
    }
};
