<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('pages::campaigns.world-rules-manager')
        ->assertStatus(200);
});
