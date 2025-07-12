<?php

namespace App\Domain\QrCode\ValueObjects;

use App\Domain\QrCode\Exceptions\InvalidFormatException;

class Color
{
    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $this->normalize($value);
    }

    private function validate(string $value): void
    {
        $normalizedValue = $this->normalize($value);
        
        // Validate hex color format
        if (!preg_match('/^#[0-9a-f]{6}$/i', $normalizedValue)) {
            throw new InvalidFormatException("Invalid color format: {$value}. Expected hex format like #000000");
        }
    }

    private function normalize(string $value): string
    {
        // Convert named colors to hex
        $namedColors = [
            'black' => '#000000',
            'white' => '#ffffff',
            'red' => '#ff0000',
            'green' => '#008000',
            'blue' => '#0000ff',
            'yellow' => '#ffff00',
            'cyan' => '#00ffff',
            'magenta' => '#ff00ff',
        ];

        $lowerValue = strtolower($value);
        if (isset($namedColors[$lowerValue])) {
            return $namedColors[$lowerValue];
        }

        // Add # prefix if missing
        if (!str_starts_with($value, '#')) {
            $value = '#' . $value;
        }

        return strtolower($value);
    }

    public static function black(): self
    {
        return new self('#000000');
    }

    public static function white(): self
    {
        return new self('#ffffff');
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getRgbArray(): array
    {
        $hex = ltrim($this->value, '#');
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
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