<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('factions.faction-card')
        ->assertStatus(200);
});
