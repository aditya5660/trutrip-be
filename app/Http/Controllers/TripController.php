<?php

/**
 * @OA\Info(
 *     title="Trip API",
 *     version="1.0.0",
 *     description="This is a RESTful API for managing trips in a travel planner application.",
 *     @OA\Contact(
 *         email="aditya5660@gmail.com"
 *     ),
 * )
 */

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

    /**
     * @OA\Get(
     *     path="/api/trips",
     *     summary="Get list of user's trips",
     *     tags={"Trips"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of trips",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Fugiat rerum ut alias laborum aut debitis."),
     *                     @OA\Property(property="origin", type="string", example="Randihaven"),
     *                     @OA\Property(property="destination", type="string", example="North Kristoffer"),
     *                     @OA\Property(property="start_date", type="string", format="date", example="2002-09-11"),
     *                     @OA\Property(property="end_date", type="string", format="date", example="2007-08-02"),
     *                     @OA\Property(property="trip_type", type="string", example="multi_day"),
     *                     @OA\Property(property="description", type="string", example="Labore est quia enim unde. Ex non ducimus ut velit quam quibusdam vitae. Consequatur voluptatum debitis quo veniam officiis soluta."),
     *                     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-03T02:06:03.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-03T02:06:03.000000Z"),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $trips = $this->tripService->getUserTrips($request->user()->id);
        return response()->json(['data' => $trips], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/trips",
     *     summary="Create a new trip",
     *     tags={"Trips"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="My Trip"),
     *             @OA\Property(property="origin", type="string", example="City A"),
     *             @OA\Property(property="destination", type="string", example="City B"),
     *             @OA\Property(property="start_date", type="string", format="date", example="2024-09-01"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2024-09-05"),
     *             @OA\Property(property="trip_type", type="string", example="multi_day"),
     *             @OA\Property(property="description", type="string", example="This is my trip description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Trip created successfully",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to create trip"
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/trips/{id}",
     *     summary="Update an existing trip",
     *     tags={"Trips"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Trip ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Trip"),
     *             @OA\Property(property="origin", type="string", example="City A"),
     *             @OA\Property(property="destination", type="string", example="City B"),
     *             @OA\Property(property="start_date", type="string", format="date", example="2024-09-01"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2024-09-06"),
     *             @OA\Property(property="trip_type", type="string", example="multi_day"),
     *             @OA\Property(property="description", type="string", example="Updated description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Trip updated successfully",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Trip not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to update trip"
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/trips/{id}",
     *     summary="Delete a trip",
     *     tags={"Trips"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Trip ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Trip deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Trip deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Trip not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to delete trip"
     *     )
     * )
     */
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
