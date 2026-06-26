<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FuelLog;
use App\Services\SoapAuditService;
use App\Services\RabbitMQPublisherService;
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
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Data retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "vehicle_id", type: "integer", example: 1),
                                    new OA\Property(property: "driver_name", type: "string", example: "Adwitiya Tikta Pramasti"),
                                    new OA\Property(property: "liters", type: "number", example: 40.5),
                                    new OA\Property(property: "total_cost", type: "number", example: 350000),
                                    new OA\Property(property: "fuel_station", type: "string", example: "SPBU Pertamina Bandung"),
                                    new OA\Property(property: "filled_at", type: "string", example: "2026-05-16 10:00:00")
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Unauthorized. X-IAE-KEY tidak valid."),
                        new OA\Property(property: "errors", nullable: true)
                    ]
                )
            )
        ]
    )]
    public function index()
    {
        $logs = FuelLog::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'data' => $logs,
            'meta' => [
                'service_name' => 'FuelLog-Service',
                'api_version' => 'v1'
            ]
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
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Data retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "vehicle_id", type: "integer", example: 1),
                                new OA\Property(property: "driver_name", type: "string", example: "Adwitiya Tikta Pramasti"),
                                new OA\Property(property: "liters", type: "number", example: 40.5),
                                new OA\Property(property: "total_cost", type: "number", example: 350000),
                                new OA\Property(property: "fuel_station", type: "string", example: "SPBU Pertamina Bandung"),
                                new OA\Property(property: "filled_at", type: "string", example: "2026-05-16 10:00:00")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Not Found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Fuel log not found"),
                        new OA\Property(property: "errors", nullable: true)
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Unauthorized. X-IAE-KEY tidak valid."),
                        new OA\Property(property: "errors", nullable: true)
                    ]
                )
            )
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
            'data' => $log,
            'meta' => [
                'service_name' => 'FuelLog-Service',
                'api_version' => 'v1'
            ]
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
                required: [
                    "vehicle_id",
                    "driver_name",
                    "liters",
                    "total_cost",
                    "fuel_station",
                    "filled_at"
                ],
                properties: [
                    new OA\Property(property: "vehicle_id", type: "integer", example: 1),
                    new OA\Property(property: "driver_name", type: "string", example: "Adwitiya Tikta Pramasti"),
                    new OA\Property(property: "liters", type: "number", example: 40.5),
                    new OA\Property(property: "total_cost", type: "number", example: 350000),
                    new OA\Property(property: "fuel_station", type: "string", example: "SPBU Pertamina Bandung"),
                    new OA\Property(property: "filled_at", type: "string", example: "2026-05-16 10:00:00")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Created",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Fuel log berhasil ditambahkan."),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "vehicle_id", type: "integer", example: 1),
                                new OA\Property(property: "driver_name", type: "string", example: "Adwitiya Tikta Pramasti"),
                                new OA\Property(property: "liters", type: "number", example: 40.5),
                                new OA\Property(property: "total_cost", type: "number", example: 350000),
                                new OA\Property(property: "fuel_station", type: "string", example: "SPBU Pertamina Bandung"),
                                new OA\Property(property: "filled_at", type: "string", example: "2026-05-16 10:00:00")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "Unauthorized. X-IAE-KEY tidak valid."),
                        new OA\Property(property: "errors", nullable: true)
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation Error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "error"),
                        new OA\Property(property: "message", type: "string", example: "The given data was invalid."),
                        new OA\Property(property: "errors", type: "object")
                    ]
                )
            )
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

        try {
            $soapService = new SoapAuditService();
            $receiptNumber = $soapService->sendAudit($log);
            $log->update(['soap_receipt_number' => $receiptNumber]);
        } catch (\Exception $e) {
            // Tidak gagalkan request jika SOAP error
        }

        try {
            $rabbitService = new RabbitMQPublisherService();
            $rabbitService->publish($log);
        } catch (\Exception $e) {
            // Tidak gagalkan request jika RabbitMQ error
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Fuel log berhasil ditambahkan.',
            'data' => $log,
            'meta' => [
                'service_name' => 'FuelLog-Service',
                'api_version' => 'v1'
            ]
        ], 201);
    }
}