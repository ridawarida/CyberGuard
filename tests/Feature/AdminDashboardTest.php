<?php

use App\Models\HelpCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows only admins to open the admin dashboard', function () {
    $this->get('/admin/dashboard')->assertRedirect(route('staff.login'));

    $moderator = User::factory()->create(['role' => 'moderator']);
    $this->actingAs($moderator)->get('/admin/dashboard')->assertForbidden();

    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
});

it('lets an admin register a help center with multiple hotlines', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post(route('admin.help-directory.store'), [
        'name' => 'Regional Crisis Clinic',
        'type' => 'clinic',
        'address' => '10 Main Road',
        'city' => 'Dhaka',
        'state' => 'Dhaka',
        'zip_code' => '1205',
        'working_hours' => ['monday' => '9 AM - 5 PM'],
        'hotlines' => [
            ['name' => 'Day line', 'phone_number' => '1001', 'operating_hours' => ['all' => '9 AM - 5 PM']],
            ['name' => 'Night line', 'phone_number' => '1002', 'is_toll_free' => '1', 'operating_hours' => ['all' => '24/7']],
        ],
    ]);

    $response->assertRedirect(route('admin.help-directory.index'));
    $center = HelpCenter::with('hotlines')->where('name', 'Regional Crisis Clinic')->firstOrFail();
    expect($center->hotlines)->toHaveCount(2)
        ->and($center->city)->toBe('Dhaka')
        ->and($center->working_hours['monday'])->toBe('9 AM - 5 PM');
});

it('lets an admin update and delete a help center', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $center = HelpCenter::create([
        'name' => 'Old Center',
        'type' => 'clinic',
        'city' => 'Dhaka',
        'working_hours' => [],
        'is_active' => true,
    ]);

    $this->actingAs($admin)->put(route('admin.help-directory.update', $center), [
        'name' => 'Verified Center',
        'type' => 'crisis_center',
        'city' => 'Chattogram',
        'working_hours' => ['monday' => 'Closed'],
        'hotlines' => [['phone_number' => '999']],
    ])->assertRedirect(route('admin.help-directory.index'));

    expect($center->fresh()->name)->toBe('Verified Center')
        ->and($center->fresh()->city)->toBe('Chattogram')
        ->and($center->fresh()->hotlines)->toHaveCount(1);

    $this->actingAs($admin)->delete(route('admin.help-directory.destroy', $center))
        ->assertRedirect(route('admin.help-directory.index'));

    expect(HelpCenter::find($center->id))->toBeNull();
});
