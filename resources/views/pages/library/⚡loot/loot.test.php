<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('pages::library.loot')
        ->assertStatus(200);
});
