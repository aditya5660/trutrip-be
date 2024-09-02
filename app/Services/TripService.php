<?php

namespace App\Services;

use App\Repositories\TripRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TripService
{
    protected $tripRepository;

    public function __construct(TripRepository $tripRepository)
    {
        $this->tripRepository = $tripRepository;
    }

    public function createTrip(array $data)
    {
        return DB::transaction(function () use ($data) {
            $trip = $this->tripRepository->create($data);
            // Clear the cache for this user's trips
            Cache::forget('xuser_trips_' . $data['user_id']);
            return $trip;
        });
    }

    public function updateTrip($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $trip = $this->tripRepository->find($id);
            if ($trip) {
                $updatedTrip = $this->tripRepository->update($trip->id, $data);
                // Clear the cache for this user's trips
                Cache::forget('xuser_trips_' . $trip->user_id);
                return $updatedTrip;
            }
            return null;
        });
    }

    public function deleteTrip($id)
    {
        return DB::transaction(function () use ($id) {
            $trip = $this->tripRepository->find($id);
            if ($trip) {
                $deleted = $this->tripRepository->delete($trip->id);
                if ($deleted) {
                    // Clear the cache for this user's trips
                    Cache::forget('xuser_trips_' . $trip->user_id);
                }
                return $deleted;
            }
            return false;
        });
    }

    public function getUserTrips($userId)
    {
        return Cache::remember("xuser_trips_$userId", now()->addMinutes(2), function () use ($userId) {
            return $this->tripRepository->allByUser($userId);
        });
    }



}
