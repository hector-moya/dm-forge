<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('pages::campaigns.location-manager')
        ->assertStatus(200);
});
