<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('allows an admin to log in and receive a token', function () {
    User::query()->delete();

    User::factory()->create([
        'name' => 'System Admin',
        'email' => 'admin@cyberguard.com',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'admin@cyberguard.com',
        'password' => 'admin123',
    ]);

    $response
        ->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.user.role', 'admin')
        ->assertJsonPath('data.token', fn ($token) => is_string($token) && $token !== '');
});
