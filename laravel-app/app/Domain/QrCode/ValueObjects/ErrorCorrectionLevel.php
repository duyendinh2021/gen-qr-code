<?php

namespace App\Domain\QrCode\ValueObjects;

use App\Domain\QrCode\Exceptions\InvalidFormatException;

class ErrorCorrectionLevel
{
    public const LOW = 'L';
    public const MEDIUM = 'M';
    public const QUARTILE = 'Q';
    public const HIGH = 'H';

    private string $value;

    public function __construct(string $value)
    {
        $value = strtoupper($value);
        $this->validate($value);
        $this->value = $value;
    }

    private function validate(string $value): void
    {
        $validLevels = [self::LOW, self::MEDIUM, self::QUARTILE, self::HIGH];
        
        if (!in_array($value, $validLevels)) {
            throw new InvalidFormatException(
                "Invalid error correction level: {$value}. Valid levels are: " . implode(', ', $validLevels)
            );
        }
    }

    public static function low(): self
    {
        return new self(self::LOW);
    }

    public static function medium(): self
    {
        return new self(self::MEDIUM);
    }

    public static function quartile(): self
    {
        return new self(self::QUARTILE);
    }

    public static function high(): self
    {
        return new self(self::HIGH);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getDescription(): string
    {
        return match ($this->value) {
            self::LOW => 'Low (~7%)',
            self::MEDIUM => 'Medium (~15%)',
            self::QUARTILE => 'Quartile (~25%)',
            self::HIGH => 'High (~30%)',
        };
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