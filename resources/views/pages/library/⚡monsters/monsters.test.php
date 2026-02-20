<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('pages::library.monsters')
        ->assertStatus(200);
});
