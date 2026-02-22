<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('pages::campaigns.lore-manager')
        ->assertStatus(200);
});
