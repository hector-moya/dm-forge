<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('sessions.consequence-form')
        ->assertStatus(200);
});
