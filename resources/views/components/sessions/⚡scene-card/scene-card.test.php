<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('sessions.scene-card')
        ->assertStatus(200);
});
