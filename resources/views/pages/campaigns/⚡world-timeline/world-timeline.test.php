<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('pages::campaigns.world-timeline')
        ->assertStatus(200);
});
