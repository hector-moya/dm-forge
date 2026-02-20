<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('pages::characters.form')
        ->assertStatus(200);
});
