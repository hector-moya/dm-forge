<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('pages::sessions.index')
        ->assertStatus(200);
});
