<?php

namespace App\Domain\QrCode\ValueObjects;

use App\Domain\QrCode\Exceptions\InvalidFormatException;

class DotStyle
{
    public const SQUARE = 'square';
    public const CIRCLE = 'circle';
    public const ROUNDED = 'rounded';

    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    private function validate(string $value): void
    {
        $validStyles = [self::SQUARE, self::CIRCLE, self::ROUNDED];
        
        if (!in_array($value, $validStyles)) {
            throw new InvalidFormatException(
                "Invalid dot style: {$value}. Valid styles are: " . implode(', ', $validStyles)
            );
        }
    }

    public static function square(): self
    {
        return new self(self::SQUARE);
    }

    public static function circle(): self
    {
        return new self(self::CIRCLE);
    }

    public static function rounded(): self
    {
        return new self(self::ROUNDED);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isSquare(): bool
    {
        return $this->value === self::SQUARE;
    }

    public function isCircle(): bool
    {
        return $this->value === self::CIRCLE;
    }

    public function isRounded(): bool
    {
        return $this->value === self::ROUNDED;
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