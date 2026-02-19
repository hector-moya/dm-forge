<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('pages::campaigns.npc-manager')
        ->assertStatus(200);
});
