<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('pages::campaigns.faction-manager')
        ->assertStatus(200);
});
