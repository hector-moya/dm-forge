<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('pages::campaigns.wizard')
        ->assertStatus(200);
});
