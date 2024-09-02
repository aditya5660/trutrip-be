<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TripManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for authenticated requests
        $this->user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);
    }

    /** @test */
    public function authenticated_user_can_create_a_trip()
    {
        $response = $this->actingAs($this->user)->postJson('/api/trips', [
            'title' => 'Test Trip',
            'origin' => 'City A',
            'destination' => 'City B',
            'start_date' => '2024-09-01',
            'end_date' => '2024-09-05',
            'trip_type' => 'multi_day',
            'description' => 'Test Description',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'title', 'origin', 'destination', 'start_date', 'end_date', 'description'
                ]
            ]);

        $this->assertDatabaseHas('trips', [
            'title' => 'Test Trip',
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function authenticated_user_can_update_a_trip()
    {
        $trip = Trip::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->putJson('/api/trips/' . $trip->id, [
            'title' => 'Updated Trip',
            'origin' => 'City A',
            'destination' => 'City B',
            'start_date' => '2024-09-01',
            'end_date' => '2024-09-06',
            'trip_type' => 'multi_day',
            'description' => 'Updated Description',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => 1
            ]);

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'title' => 'Updated Trip',
        ]);
    }

    /** @test */
    public function authenticated_user_can_delete_a_trip()
    {
        $trip = Trip::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->deleteJson('/api/trips/' . $trip->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Trip deleted',
            ]);


    }

    /** @test */
    public function authenticated_user_can_list_their_trips()
    {
        $trip = Trip::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/trips');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'title', 'origin', 'destination', 'start_date', 'end_date', 'trip_type', 'description'
                    ]
                ]
            ]);
    }

    /** @test */
    public function unauthorized_user_cannot_access_trip_endpoints()
    {
        $response = $this->postJson('/api/trips', [
            'title' => 'Test Trip',
            'origin' => 'City A',
            'destination' => 'City B',
            'start_date' => '2024-09-01',
            'end_date' => '2024-09-05',
            'trip_type' => 'multi_day',
            'description' => 'Test Description',
        ]);

        $response->assertStatus(401);

        $trip = Trip::factory()->create();

        $response = $this->putJson('/api/trips/' . $trip->id, [
            'title' => 'Updated Trip',
        ]);

        $response->assertStatus(401);

        $response = $this->deleteJson('/api/trips/' . $trip->id);

        $response->assertStatus(401);
    }
}
