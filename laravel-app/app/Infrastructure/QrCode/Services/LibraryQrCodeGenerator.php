<?php

namespace App\Infrastructure\QrCode\Services;

use App\Domain\QrCode\ValueObjects\Content;
use App\Domain\QrCode\Entities\QrCodeConfiguration;
use App\Domain\QrCode\Services\QrCodeGeneratorServiceInterface;
use App\Domain\QrCode\Exceptions\GenerationFailedException;
use App\Domain\QrCode\ValueObjects\FileType;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\PdfWriter;

class LibraryQrCodeGenerator implements QrCodeGeneratorServiceInterface
{
    private bool $libraryAvailable;

    public function __construct()
    {
        $this->libraryAvailable = class_exists('Endroid\QrCode\Builder\Builder');
    }

    public function generate(Content $content, QrCodeConfiguration $configuration): string
    {
        if (!$this->libraryAvailable) {
            throw new GenerationFailedException(
                'QR Code library not available. Please install endroid/qr-code package: composer install'
            );
        }

        try {
            $builder = $this->createBuilder($content, $configuration);
            $result = $builder->build();

            return match ($configuration->getFileType()->getValue()) {
                FileType::PNG, FileType::JPG, FileType::SVG, FileType::PDF => $result->getString(),
                default => throw new GenerationFailedException(
                    'Unsupported file type: ' . $configuration->getFileType()->getValue()
                ),
            };
        } catch (\Exception $e) {
            throw new GenerationFailedException(
                'Failed to generate QR code: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    public function supports(QrCodeConfiguration $configuration): bool
    {
        if (!$this->libraryAvailable) {
            return false;
        }

        return in_array(
            $configuration->getFileType()->getValue(),
            $this->getSupportedFileTypes()
        );
    }

    public function getSupportedFileTypes(): array
    {
        return [FileType::PNG, FileType::JPG, FileType::SVG, FileType::PDF];
    }

    private function createBuilder(Content $content, QrCodeConfiguration $configuration)
    {
        if (!$this->libraryAvailable) {
            throw new GenerationFailedException('QR Code library not available');
        }

        $builder = Builder::create()
            ->data($content->getValue())
            ->encoding(new Encoding('UTF-8'))
            ->size($configuration->getSize()->getWidth())
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin);

        // Set error correction level
        $builder->errorCorrectionLevel($this->mapErrorCorrectionLevel($configuration));

        // Set colors
        $foregroundColor = $this->parseColor($configuration->getColor()->getValue());
        $backgroundColor = $this->parseColor($configuration->getBackground()->getValue());
        
        $builder->foregroundColor($foregroundColor);
        $builder->backgroundColor($backgroundColor);

        // Set writer based on file type
        $writer = $this->getWriter($configuration->getFileType());
        $builder->writer($writer);

        return $builder;
    }

    private function mapErrorCorrectionLevel(QrCodeConfiguration $configuration): ErrorCorrectionLevel
    {
        $level = $configuration->getErrorCorrectionLevel()->getValue();
        
        return match ($level) {
            'L' => ErrorCorrectionLevel::Low,
            'M' => ErrorCorrectionLevel::Medium,
            'Q' => ErrorCorrectionLevel::Quartile,
            'H' => ErrorCorrectionLevel::High,
            default => ErrorCorrectionLevel::Medium,
        };
    }

    private function parseColor(string $hexColor): Color
    {
        $hex = ltrim($hexColor, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        return new Color($r, $g, $b);
    }

    private function getWriter(FileType $fileType)
    {
        return match ($fileType->getValue()) {
            FileType::PNG => new PngWriter(),
            FileType::JPG => new PngWriter(), // Use PNG writer for JPG too
            FileType::SVG => new SvgWriter(),
            FileType::PDF => new PdfWriter(),
            default => new PngWriter(),
        };
    }
}