<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FuelLog;
use App\Services\SoapAuditService;
use App\Services\RabbitMQPublisherService;
use Illuminate\Http\Request;

class FuelLogController extends Controller
{
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
        ], 200);
    }

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
        ], 200);
    }

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
            $log->update([
                'soap_receipt_number' => $receiptNumber
            ]);
        } catch (\Exception $e) {
            // Request tetap berhasil walaupun SOAP audit gagal
        }

        try {
            $rabbitService = new RabbitMQPublisherService();
            $rabbitService->publish($log);
        } catch (\Exception $e) {
            // Request tetap berhasil walaupun RabbitMQ gagal
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Fuel log berhasil ditambahkan.',
            'data' => $log
        ], 201);
    }
}