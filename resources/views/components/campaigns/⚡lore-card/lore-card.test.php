<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('campaigns.lore-card')
        ->assertStatus(200);
});
