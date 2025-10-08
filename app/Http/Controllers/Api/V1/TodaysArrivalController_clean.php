<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TodaysArrivalController extends Controller
{
    /**
     * Main API endpoint for today's arrivals
     */
    public function index(): JsonResponse
    {
        // Force disable any output buffering
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        try {
            $response = [
                'arrivals' => [],
                'status' => true,
                'message' => 'Today\'s arrivals retrieved successfully',
                'count' => 0,
                'debug' => 'Clean response test'
            ];

            // Force UTF-8 encoding and proper headers
            return response()
                ->json($response, 200)
                ->header('Content-Type', 'application/json; charset=UTF-8')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            // Force disable any output buffering for error response too
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            $errorResponse = [
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'arrivals' => [],
                'debug' => 'Error response test'
            ];

            return response()
                ->json($errorResponse, 500)
                ->header('Content-Type', 'application/json; charset=UTF-8')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        }
    }

    /**
     * Simple test endpoint
     */
    public function simple()
    {
        // Force disable any output buffering
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        return response()
            ->json(['test' => 'success', 'timestamp' => time()], 200)
            ->header('Content-Type', 'application/json; charset=UTF-8');
    }

    /**
     * Minimal test endpoint with static data
     */
    public function minimal(): JsonResponse
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        $response = [
            'arrivals' => [
                [
                    'id' => 1,
                    'title' => 'Test Arrival 1',
                    'description' => 'This is a test arrival',
                    'image' => '',
                    'branch_id' => 1,
                    'created_at' => '2024-01-01 10:00:00',
                    'updated_at' => '2024-01-01 10:00:00'
                ]
            ],
            'status' => true,
            'message' => 'Minimal test data retrieved successfully'
        ];

        return response()
            ->json($response, 200)
            ->header('Content-Type', 'application/json; charset=UTF-8')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    /**
     * Debug endpoint to check response
     */
    public function debug(): JsonResponse
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        $debug = [
            'php_version' => phpversion(),
            'encoding' => mb_internal_encoding(),
            'ob_level' => ob_get_level(),
            'headers_sent' => headers_sent(),
            'status' => true,
            'message' => 'Debug information'
        ];

        return response()
            ->json($debug, 200)
            ->header('Content-Type', 'application/json; charset=UTF-8');
    }
}