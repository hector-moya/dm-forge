<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('pages::campaigns.special-mechanics-manager')
        ->assertStatus(200);
});
