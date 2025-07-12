<?php

namespace App\Application\QrCode\DTOs;

use App\Domain\QrCode\Entities\QrCode;

class QrCodeGenerationResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $qrCode = null,
        public readonly ?string $format = null,
        public readonly ?string $size = null,
        public readonly bool $cached = false,
        public readonly float $generationTimeMs = 0.0,
        public readonly ?string $filePath = null,
        public readonly ?string $fileUrl = null,
        public readonly ?array $errors = null,
        public readonly ?string $message = null,
    ) {}

    public static function success(
        string $qrCodeData,
        QrCode $qrCode,
        float $generationTimeMs,
        bool $cached = false,
        ?string $filePath = null,
        ?string $fileUrl = null
    ): self {
        return new self(
            success: true,
            qrCode: $qrCodeData,
            format: $qrCode->getConfiguration()->getFileType()->getValue(),
            size: $qrCode->getConfiguration()->getSize()->__toString(),
            cached: $cached,
            generationTimeMs: $generationTimeMs,
            filePath: $filePath,
            fileUrl: $fileUrl,
        );
    }

    public static function error(array $errors, string $message = 'QR code generation failed'): self
    {
        return new self(
            success: false,
            errors: $errors,
            message: $message,
        );
    }

    public static function validationError(array $errors): self
    {
        return new self(
            success: false,
            errors: $errors,
            message: 'Validation failed',
        );
    }

    public function toArray(): array
    {
        if (!$this->success) {
            return [
                'success' => false,
                'message' => $this->message,
                'errors' => $this->errors,
            ];
        }

        $data = [
            'success' => true,
            'data' => array_filter([
                'qr_code' => $this->qrCode,
                'format' => $this->format,
                'size' => $this->size,
                'cached' => $this->cached,
                'generation_time_ms' => $this->generationTimeMs,
                'file_path' => $this->filePath,
                'file_url' => $this->fileUrl,
            ], fn($value) => $value !== null),
        ];

        return $data;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    public function getHttpStatusCode(): int
    {
        if (!$this->success) {
            if ($this->errors && count($this->errors) > 0) {
                return 422; // Validation error
            }
            return 500; // Server error
        }
        
        return 200; // Success
    }
}