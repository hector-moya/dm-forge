<?php

use App\Models\Campaign;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('renders the dashboard for authenticated users', function () {
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test('pages::dashboard')
        ->assertStatus(200)
        ->assertSee('My Campaigns');
});

it('shows empty state when user has no campaigns', function () {
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test('pages::dashboard')
        ->assertSee('No campaigns yet');
});

it('shows campaigns when user has campaigns', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['user_id' => $user->id, 'name' => 'Test Campaign']);

    actingAs($user);

    Livewire::test('pages::dashboard')
        ->assertSee('Test Campaign');
});
