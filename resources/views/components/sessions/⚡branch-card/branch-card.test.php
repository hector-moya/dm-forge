<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('sessions.branch-card')
        ->assertStatus(200);
});
