<?php

namespace App\Domain\QrCode\ValueObjects;

use App\Domain\QrCode\Exceptions\InvalidFormatException;

class FileType
{
    public const PNG = 'png';
    public const JPG = 'jpg';
    public const JPEG = 'jpeg';
    public const SVG = 'svg';
    public const PDF = 'pdf';

    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower($value);
        $this->validate($value);
        $this->value = $this->normalize($value);
    }

    private function validate(string $value): void
    {
        $validTypes = [self::PNG, self::JPG, self::JPEG, self::SVG, self::PDF];
        
        if (!in_array($value, $validTypes)) {
            throw new InvalidFormatException(
                "Invalid file type: {$value}. Valid types are: " . implode(', ', $validTypes)
            );
        }
    }

    private function normalize(string $value): string
    {
        // Normalize jpeg to jpg
        return $value === self::JPEG ? self::JPG : $value;
    }

    public static function png(): self
    {
        return new self(self::PNG);
    }

    public static function jpg(): self
    {
        return new self(self::JPG);
    }

    public static function svg(): self
    {
        return new self(self::SVG);
    }

    public static function pdf(): self
    {
        return new self(self::PDF);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getMimeType(): string
    {
        return match ($this->value) {
            self::PNG => 'image/png',
            self::JPG => 'image/jpeg',
            self::SVG => 'image/svg+xml',
            self::PDF => 'application/pdf',
            default => 'application/octet-stream',
        };
    }

    public function isRaster(): bool
    {
        return in_array($this->value, [self::PNG, self::JPG]);
    }

    public function isVector(): bool
    {
        return in_array($this->value, [self::SVG, self::PDF]);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}