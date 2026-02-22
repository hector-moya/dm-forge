<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('campaigns.lore-details')
        ->assertStatus(200);
});
