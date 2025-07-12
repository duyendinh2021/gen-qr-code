<?php

namespace App\Domain\QrCode\ValueObjects;

use App\Domain\QrCode\Exceptions\InvalidFormatException;

class Size
{
    private int $width;
    private int $height;

    public function __construct(int $width, int $height = null)
    {
        $height = $height ?? $width; // Square by default
        $this->validate($width, $height);
        $this->width = $width;
        $this->height = $height;
    }

    public static function fromString(string $size): self
    {
        if (preg_match('/^(\d+)x(\d+)$/', $size, $matches)) {
            return new self((int)$matches[1], (int)$matches[2]);
        }

        if (is_numeric($size)) {
            return new self((int)$size);
        }

        throw new InvalidFormatException("Invalid size format: {$size}. Expected format: '300x300' or '300'");
    }

    private function validate(int $width, int $height): void
    {
        if ($width < 50 || $width > 2000) {
            throw new InvalidFormatException('Width must be between 50 and 2000 pixels');
        }

        if ($height < 50 || $height > 2000) {
            throw new InvalidFormatException('Height must be between 50 and 2000 pixels');
        }
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function __toString(): string
    {
        return "{$this->width}x{$this->height}";
    }

    public function equals(self $other): bool
    {
        return $this->width === $other->width && $this->height === $other->height;
    }
}