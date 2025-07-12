<?php

namespace App\Domain\QrCode\Entities;

use App\Domain\QrCode\ValueObjects\Size;
use App\Domain\QrCode\ValueObjects\Color;
use App\Domain\QrCode\ValueObjects\DotStyle;
use App\Domain\QrCode\ValueObjects\FileType;
use App\Domain\QrCode\ValueObjects\OutputType;
use App\Domain\QrCode\ValueObjects\ErrorCorrectionLevel;

class QrCodeConfiguration
{
    public function __construct(
        private Size $size,
        private DotStyle $dotStyle,
        private Color $color,
        private Color $background,
        private FileType $fileType,
        private OutputType $outputType,
        private ErrorCorrectionLevel $errorCorrectionLevel
    ) {}

    public static function default(): self
    {
        return new self(
            new Size(300),
            DotStyle::square(),
            Color::black(),
            Color::white(),
            FileType::png(),
            OutputType::base64(),
            ErrorCorrectionLevel::medium()
        );
    }

    public function withSize(Size $size): self
    {
        return new self(
            $size,
            $this->dotStyle,
            $this->color,
            $this->background,
            $this->fileType,
            $this->outputType,
            $this->errorCorrectionLevel
        );
    }

    public function withDotStyle(DotStyle $dotStyle): self
    {
        return new self(
            $this->size,
            $dotStyle,
            $this->color,
            $this->background,
            $this->fileType,
            $this->outputType,
            $this->errorCorrectionLevel
        );
    }

    public function withColor(Color $color): self
    {
        return new self(
            $this->size,
            $this->dotStyle,
            $color,
            $this->background,
            $this->fileType,
            $this->outputType,
            $this->errorCorrectionLevel
        );
    }

    public function withBackground(Color $background): self
    {
        return new self(
            $this->size,
            $this->dotStyle,
            $this->color,
            $background,
            $this->fileType,
            $this->outputType,
            $this->errorCorrectionLevel
        );
    }

    public function withFileType(FileType $fileType): self
    {
        return new self(
            $this->size,
            $this->dotStyle,
            $this->color,
            $this->background,
            $fileType,
            $this->outputType,
            $this->errorCorrectionLevel
        );
    }

    public function withOutputType(OutputType $outputType): self
    {
        return new self(
            $this->size,
            $this->dotStyle,
            $this->color,
            $this->background,
            $this->fileType,
            $outputType,
            $this->errorCorrectionLevel
        );
    }

    public function withErrorCorrectionLevel(ErrorCorrectionLevel $errorCorrectionLevel): self
    {
        return new self(
            $this->size,
            $this->dotStyle,
            $this->color,
            $this->background,
            $this->fileType,
            $this->outputType,
            $errorCorrectionLevel
        );
    }

    public function getSize(): Size
    {
        return $this->size;
    }

    public function getDotStyle(): DotStyle
    {
        return $this->dotStyle;
    }

    public function getColor(): Color
    {
        return $this->color;
    }

    public function getBackground(): Color
    {
        return $this->background;
    }

    public function getFileType(): FileType
    {
        return $this->fileType;
    }

    public function getOutputType(): OutputType
    {
        return $this->outputType;
    }

    public function getErrorCorrectionLevel(): ErrorCorrectionLevel
    {
        return $this->errorCorrectionLevel;
    }

    public function getHash(): string
    {
        return md5(serialize([
            $this->size->__toString(),
            $this->dotStyle->getValue(),
            $this->color->getValue(),
            $this->background->getValue(),
            $this->fileType->getValue(),
            $this->outputType->getValue(),
            $this->errorCorrectionLevel->getValue(),
        ]));
    }
}