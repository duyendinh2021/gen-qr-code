<?php

namespace App\Application\QrCode\UseCases;

use App\Application\QrCode\DTOs\QrCodeGenerationRequest;
use App\Application\QrCode\DTOs\QrCodeGenerationResponse;
use App\Domain\QrCode\Entities\QrCode;
use App\Domain\QrCode\Entities\QrCodeConfiguration;
use App\Domain\QrCode\ValueObjects\Content;
use App\Domain\QrCode\ValueObjects\Size;
use App\Domain\QrCode\ValueObjects\Color;
use App\Domain\QrCode\ValueObjects\DotStyle;
use App\Domain\QrCode\ValueObjects\FileType;
use App\Domain\QrCode\ValueObjects\OutputType;
use App\Domain\QrCode\ValueObjects\ErrorCorrectionLevel;
use App\Domain\QrCode\Services\QrCodeGeneratorServiceInterface;
use App\Domain\QrCode\Services\QrCodeCacheService;
use App\Domain\QrCode\Events\QrCodeGenerated;
use App\Infrastructure\QrCode\Services\FileStorageService;
use App\Domain\QrCode\Exceptions\InvalidContentException;
use App\Domain\QrCode\Exceptions\InvalidFormatException;
use App\Domain\QrCode\Exceptions\GenerationFailedException;
use Illuminate\Support\Facades\Log;

class GenerateQrCodeUseCase
{
    public function __construct(
        private QrCodeGeneratorServiceInterface $generator,
        private QrCodeCacheService $cacheService,
        private FileStorageService $storageService,
    ) {}

    public function execute(QrCodeGenerationRequest $request): QrCodeGenerationResponse
    {
        $startTime = microtime(true);

        try {
            // Validate request
            $validationErrors = $request->validate();
            if (!empty($validationErrors)) {
                return QrCodeGenerationResponse::validationError($validationErrors);
            }

            // Create domain objects
            $content = new Content($request->content);
            $configuration = $this->createConfiguration($request);
            $qrCode = new QrCode($content, $configuration);

            // Check cache first
            $cachedQrCode = $this->cacheService->get($qrCode->getCacheKey());
            if ($cachedQrCode && $cachedQrCode->getData()) {
                $generationTime = (microtime(true) - $startTime) * 1000;
                
                return $this->createSuccessResponse(
                    $cachedQrCode->getData(),
                    $cachedQrCode,
                    $generationTime,
                    true
                );
            }

            // Generate QR code
            $qrCodeData = $this->generator->generate($content, $configuration);
            $qrCode->setData($qrCodeData);

            // Handle output type
            $response = $this->handleOutputType($qrCode, $qrCodeData);

            // Cache the result
            $this->cacheService->store($qrCode, 3600); // Cache for 1 hour

            // Dispatch event
            $generationTime = (microtime(true) - $startTime) * 1000;
            event(new QrCodeGenerated($qrCode, $generationTime));

            // Create response
            return $this->createSuccessResponse(
                $this->prepareResponseData($qrCode),
                $qrCode,
                $generationTime,
                false,
                $response['file_path'] ?? null,
                $response['file_url'] ?? null
            );

        } catch (InvalidContentException | InvalidFormatException $e) {
            Log::warning('QR code validation error', [
                'error' => $e->getMessage(),
                'request' => $request->toArray()
            ]);
            
            return QrCodeGenerationResponse::validationError(['validation' => $e->getMessage()]);

        } catch (GenerationFailedException $e) {
            Log::error('QR code generation failed', [
                'error' => $e->getMessage(),
                'request' => $request->toArray()
            ]);
            
            return QrCodeGenerationResponse::error(
                ['generation' => $e->getMessage()],
                'Failed to generate QR code'
            );

        } catch (\Exception $e) {
            Log::error('Unexpected error during QR code generation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->toArray()
            ]);
            
            return QrCodeGenerationResponse::error(
                ['system' => 'An unexpected error occurred'],
                'System error'
            );
        }
    }

    private function createConfiguration(QrCodeGenerationRequest $request): QrCodeConfiguration
    {
        $config = QrCodeConfiguration::default();

        if ($request->size !== null) {
            $config = $config->withSize(new Size($request->size));
        }

        if ($request->dotStyle !== null) {
            $config = $config->withDotStyle(new DotStyle($request->dotStyle));
        }

        if ($request->color !== null) {
            $config = $config->withColor(new Color($request->color));
        }

        if ($request->background !== null) {
            $config = $config->withBackground(new Color($request->background));
        }

        if ($request->fileType !== null) {
            $config = $config->withFileType(new FileType($request->fileType));
        }

        if ($request->outputType !== null) {
            $config = $config->withOutputType(new OutputType($request->outputType));
        }

        if ($request->errorCorrection !== null) {
            $config = $config->withErrorCorrectionLevel(new ErrorCorrectionLevel($request->errorCorrection));
        }

        return $config;
    }

    private function handleOutputType(QrCode $qrCode, string $qrCodeData): array
    {
        $outputType = $qrCode->getConfiguration()->getOutputType();
        $result = [];

        if ($outputType->isStorage()) {
            try {
                $filePath = $this->storageService->store($qrCode);
                $fileUrl = $this->storageService->getUrl($qrCode);
                $result['file_path'] = $filePath;
                $result['file_url'] = $fileUrl;
            } catch (\Exception $e) {
                Log::error('Failed to store QR code file', [
                    'error' => $e->getMessage(),
                    'qr_code_id' => $qrCode->getId()
                ]);
                throw new GenerationFailedException('Failed to store QR code file');
            }
        }

        return $result;
    }

    private function prepareResponseData(QrCode $qrCode): string
    {
        $outputType = $qrCode->getConfiguration()->getOutputType();
        $fileType = $qrCode->getConfiguration()->getFileType();

        if ($outputType->isBase64()) {
            $mimeType = $fileType->getMimeType();
            return 'data:' . $mimeType . ';base64,' . base64_encode($qrCode->getData());
        }

        return $qrCode->getData();
    }

    private function createSuccessResponse(
        string $qrCodeData,
        QrCode $qrCode,
        float $generationTimeMs,
        bool $cached = false,
        ?string $filePath = null,
        ?string $fileUrl = null
    ): QrCodeGenerationResponse {
        return QrCodeGenerationResponse::success(
            $qrCodeData,
            $qrCode,
            $generationTimeMs,
            $cached,
            $filePath,
            $fileUrl
        );
    }
}