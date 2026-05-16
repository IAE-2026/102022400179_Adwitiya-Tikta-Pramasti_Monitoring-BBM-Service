<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FuelLog;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Fuel Logs")]
class FuelLogController extends Controller
{
    #[OA\Get(
        path: "/api/v1/fuel-logs",
        summary: "Ambil semua fuel log",
        security: [["apiKey" => []]],
        tags: ["Fuel Logs"],
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function index()
    {
        $logs = FuelLog::all();
        return response()->json([
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'data' => $logs,
            'meta' => ['service_name' => 'FuelLog-Service', 'api_version' => 'v1']
        ]);
    }

    #[OA\Get(
        path: "/api/v1/fuel-logs/{id}",
        summary: "Ambil fuel log berdasarkan ID",
        security: [["apiKey" => []]],
        tags: ["Fuel Logs"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    public function show($id)
    {
        $log = FuelLog::find($id);
        if (!$log) {
            return response()->json([
                'status' => 'error',
                'message' => 'Fuel log not found',
                'errors' => null
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'data' => $log
        ]);
    }

    #[OA\Post(
        path: "/api/v1/fuel-logs",
        summary: "Tambah fuel log baru",
        security: [["apiKey" => []]],
        tags: ["Fuel Logs"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["vehicle_id", "driver_name", "liters", "total_cost", "fuel_station", "filled_at"],
                properties: [
                    new OA\Property(property: "vehicle_id", type: "integer", example: 1),
                    new OA\Property(property: "driver_name", type: "string", example: "Budi Santoso"),
                    new OA\Property(property: "liters", type: "number", example: 40.5),
                    new OA\Property(property: "total_cost", type: "number", example: 350000),
                    new OA\Property(property: "fuel_station", type: "string", example: "SPBU Pertamina Bandung"),
                    new OA\Property(property: "filled_at", type: "string", example: "2026-05-16 10:00:00"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 422, description: "Validation Error")
        ]
    )]
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id'   => 'required|integer',
            'driver_name'  => 'required|string',
            'liters'       => 'required|numeric|min:0',
            'total_cost'   => 'required|numeric|min:0',
            'fuel_station' => 'required|string',
            'filled_at'    => 'required|date',
        ]);

        $log = FuelLog::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Fuel log berhasil ditambahkan.',
            'data' => $log
        ], 201);
    }
}