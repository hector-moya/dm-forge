<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('pages::sessions.builder')
        ->assertStatus(200);
});
