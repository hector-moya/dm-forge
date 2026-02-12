<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('sessions.monster-form')
        ->assertStatus(200);
});
