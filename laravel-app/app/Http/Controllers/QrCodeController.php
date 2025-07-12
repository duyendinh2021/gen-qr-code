<?php

namespace App\Http\Controllers;

use App\Application\QrCode\UseCases\GenerateQrCodeUseCase;
use App\Application\QrCode\DTOs\QrCodeGenerationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class QrCodeController extends Controller
{
    public function __construct(
        private GenerateQrCodeUseCase $generateQrCodeUseCase
    ) {}

    /**
     * Generate a QR code
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generate(Request $request): JsonResponse
    {
        try {
            // Create DTO from request
            $generationRequest = QrCodeGenerationRequest::fromArray($request->all());
            
            // Execute use case
            $response = $this->generateQrCodeUseCase->execute($generationRequest);
            
            // Log the request for monitoring
            Log::info('QR code generation request', [
                'content_length' => strlen($generationRequest->content),
                'file_type' => $generationRequest->fileType ?? 'png',
                'output_type' => $generationRequest->outputType ?? 'base64',
                'success' => $response->success,
                'cached' => $response->cached,
                'generation_time_ms' => $response->generationTimeMs,
            ]);
            
            return response()->json(
                $response->toArray(),
                $response->getHttpStatusCode()
            );
            
        } catch (\Exception $e) {
            Log::error('QR code controller error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'errors' => ['system' => 'Internal server error']
            ], 500);
        }
    }

    /**
     * Get QR code generation statistics
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            // This would typically be moved to a separate use case
            // For now, we'll return basic stats
            return response()->json([
                'success' => true,
                'data' => [
                    'supported_formats' => ['png', 'jpg', 'svg', 'pdf'],
                    'supported_output_types' => ['storage', 'base64', 'stream'],
                    'max_content_length' => 4296,
                    'size_limits' => [
                        'min' => 50,
                        'max' => 2000
                    ],
                    'error_correction_levels' => ['L', 'M', 'Q', 'H'],
                    'dot_styles' => ['square', 'circle', 'rounded'],
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get QR code stats', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics'
            ], 500);
        }
    }

    /**
     * Health check endpoint
     *
     * @return JsonResponse
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0'
        ]);
    }
}