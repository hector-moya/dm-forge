<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('pages::sessions.recap')
        ->assertStatus(200);
});
