<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('pages::characters.index')
        ->assertStatus(200);
});
