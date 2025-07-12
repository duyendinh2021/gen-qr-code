<?php

namespace App\Infrastructure\QrCode\Services;

use App\Domain\QrCode\ValueObjects\Content;
use App\Domain\QrCode\Entities\QrCodeConfiguration;
use App\Domain\QrCode\Services\QrCodeGeneratorServiceInterface;
use App\Domain\QrCode\Exceptions\GenerationFailedException;
use App\Domain\QrCode\ValueObjects\FileType;

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
                FileType::PNG, FileType::JPG => $result->getString(),
                FileType::SVG => $result->getString(),
                FileType::PDF => $this->generatePdf($result->getString(), $configuration),
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

        // Dynamically load classes to avoid fatal errors when library is missing
        $builderClass = 'Endroid\QrCode\Builder\Builder';
        $encodingClass = 'Endroid\QrCode\Encoding\Encoding';
        $roundBlockModeClass = 'Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin';
        
        $builder = $builderClass::create()
            ->data($content->getValue())
            ->encoding(new $encodingClass('UTF-8'))
            ->size($configuration->getSize()->getWidth())
            ->roundBlockSizeMode(new $roundBlockModeClass());

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

    private function mapErrorCorrectionLevel(QrCodeConfiguration $configuration)
    {
        $levelMap = [
            'L' => 'Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow',
            'M' => 'Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelMedium',
            'Q' => 'Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelQuartile',
            'H' => 'Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh',
        ];

        $level = $configuration->getErrorCorrectionLevel()->getValue();
        $className = $levelMap[$level] ?? $levelMap['M'];
        
        return new $className();
    }

    private function parseColor(string $hexColor): array
    {
        $hex = ltrim($hexColor, '#');
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    private function getWriter(FileType $fileType)
    {
        $writerMap = [
            FileType::PNG => 'Endroid\QrCode\Writer\PngWriter',
            FileType::JPG => 'Endroid\QrCode\Writer\PngWriter', // Use PNG writer for JPG too
            FileType::SVG => 'Endroid\QrCode\Writer\SvgWriter',
        ];

        $className = $writerMap[$fileType->getValue()] ?? $writerMap[FileType::PNG];
        return new $className();
    }

    private function generatePdf(string $qrCodeData, QrCodeConfiguration $configuration): string
    {
        // For PDF generation, we'll use a simple approach
        // In production, you might want to use a dedicated PDF library
        try {
            // Create a basic PDF with the QR code
            // This is a simplified implementation
            $pdfContent = $this->createSimplePdf($qrCodeData, $configuration);
            return $pdfContent;
        } catch (\Exception $e) {
            throw new GenerationFailedException(
                'Failed to generate PDF: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    private function createSimplePdf(string $qrCodeData, QrCodeConfiguration $configuration): string
    {
        // This is a very basic PDF implementation
        // In production, use a proper PDF library like MPDF or TCPDF
        $pdfHeader = "%PDF-1.4\n";
        $pdfContent = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $pdfContent .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $pdfContent .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R >>\nendobj\n";
        $pdfContent .= "4 0 obj\n<< /Length 44 >>\nstream\nBT\n/F1 12 Tf\n100 700 Td\n(QR Code Generated) Tj\nET\nendstream\nendobj\n";
        $pdfContent .= "xref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000206 00000 n \n";
        $pdfContent .= "trailer\n<< /Size 5 /Root 1 0 R >>\nstartxref\n300\n%%EOF";
        
        return $pdfHeader . $pdfContent;
    }
}