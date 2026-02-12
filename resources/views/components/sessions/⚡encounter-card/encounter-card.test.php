<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('sessions.encounter-card')
        ->assertStatus(200);
});
