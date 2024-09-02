<?php

namespace App\Http\Controllers;

use App\Services\TripService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TripController extends Controller
{
    protected $tripService;

    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
    }

    public function index(Request $request)
    {
        $trips = $this->tripService->getUserTrips($request->user()->id);
        return response()->json(['data' => $trips], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'trip_type' => 'required|in:single_day,multi_day',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        try {
            $trip = $this->tripService->createTrip(array_merge(
                $request->all(),
                ['user_id' => $request->user()->id]
            ));

            return response()->json(['data' => $trip], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create trip'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'origin' => 'sometimes|required|string|max:255',
            'destination' => 'sometimes|required|string|max:255',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'trip_type' => 'sometimes|required|in:single_day,multi_day',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $trip = $this->tripService->updateTrip($id, $request->all());
            if ($trip) {
                return response()->json(['data' => $trip], 200);
            }
            return response()->json(['error' => 'Trip not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update trip'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $result = $this->tripService->deleteTrip($id);
            if ($result) {
                return response()->json(['message' => 'Trip deleted'], 200);
            }
            return response()->json(['error' => 'Trip not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete trip'], 500);
        }
    }
}
