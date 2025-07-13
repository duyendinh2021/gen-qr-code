<?php

namespace App\Domain\QrCode\Services;

use App\Domain\QrCode\ValueObjects\Content;
use App\Domain\QrCode\Entities\QrCodeConfiguration;
use App\Domain\QrCode\Exceptions\GenerationFailedException;

interface QrCodeGeneratorServiceInterface
{
    /**
     * Generate QR code data based on content and configuration
     *
     * @throws GenerationFailedException
     */
    public function generate(Content $content, QrCodeConfiguration $configuration): string;

    /**
     * Check if the generator supports the given file type
     */
    public function supports(QrCodeConfiguration $configuration): bool;

    /**
     * Get supported file types
     */
    public function getSupportedFileTypes(): array;
}