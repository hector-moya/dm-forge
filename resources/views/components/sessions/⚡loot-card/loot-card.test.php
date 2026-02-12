<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('sessions.loot-card')
        ->assertStatus(200);
});
