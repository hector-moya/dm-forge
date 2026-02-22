<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('campaigns.details-card')
        ->assertStatus(200);
});
