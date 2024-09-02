<?php

namespace Tests\Unit;

use App\Repositories\TripRepository;
use App\Services\TripService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Mockery;

class TripServiceTest extends TestCase
{
    protected $tripRepository;
    protected $tripService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tripRepository = Mockery::mock(TripRepository::class);
        $this->tripService = new TripService($this->tripRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testCreateTrip()
    {
        $data = [
            'title' => 'Test Trip',
            'origin' => 'City A',
            'destination' => 'City B',
            'start_date' => '2024-09-01',
            'end_date' => '2024-09-05',
            'trip_type' => 'multi-day',
            'description' => 'Test Description',
            'user_id' => 1,
        ];

        $this->tripRepository->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn((object) $data);

        Cache::shouldReceive('forget')
            ->once()
            ->with('xuser_trips_1');

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $result = $this->tripService->createTrip($data);

        $this->assertEquals('Test Trip', $result->title);
    }

    public function testUpdateTrip()
    {
        $data = [
            'id' => 1,
            'title' => 'Updated Trip',
            'origin' => 'City A',
            'destination' => 'City B',
            'start_date' => '2024-09-01',
            'end_date' => '2024-09-05',
            'trip_type' => 'multi-day',
            'description' => 'Updated Description',
            'user_id' => 1,
        ];

        $trip = (object) $data;

        $this->tripRepository->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($trip);

        $this->tripRepository->shouldReceive('update')
            ->once()
            ->with(1, $data)
            ->andReturn($trip);

        Cache::shouldReceive('forget')
            ->once()
            ->with('xuser_trips_1');

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $result = $this->tripService->updateTrip(1, $data);

        $this->assertEquals('Updated Trip', $result->title);
    }

    public function testDeleteTrip()
    {
        $trip = (object) [
            'id' => 1,
            'user_id' => 1,
        ];

        $this->tripRepository->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($trip);

        $this->tripRepository->shouldReceive('delete')
            ->once()
            ->with(1)
            ->andReturn(true);

        Cache::shouldReceive('forget')
            ->once()
            ->with('xuser_trips_1');

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $result = $this->tripService->deleteTrip(1);

        $this->assertTrue($result);
    }

    public function testGetUserTrips()
    {
        $userId = 1;
        $trips = [
            (object) [
                'title' => 'Test Trip',
                'origin' => 'City A',
                'destination' => 'City B',
                'start_date' => '2024-09-01',
                'end_date' => '2024-09-05',
                'trip_type' => 'multi-day',
                'description' => 'Test Description',
            ]
        ];

        Cache::shouldReceive('remember')
            ->once()
            ->with("xuser_trips_$userId", Mockery::any(), Mockery::on(function ($callback) use ($trips) {
                return $callback() === $trips;
            }))
            ->andReturn($trips);

        $this->tripRepository->shouldReceive('allByUser')
            ->once()
            ->with($userId)
            ->andReturn($trips);

        $result = $this->tripService->getUserTrips($userId);

        $this->assertCount(1, $result);
        $this->assertEquals('Test Trip', $result[0]->title);
    }
}
