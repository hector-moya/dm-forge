<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('pages::sessions.runner')
        ->assertStatus(200);
});
