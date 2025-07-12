<?php

namespace App\Domain\QrCode\ValueObjects;

use App\Domain\QrCode\Exceptions\InvalidFormatException;

class OutputType
{
    public const STORAGE = 'storage';
    public const BASE64 = 'base64';
    public const STREAM = 'stream';

    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    private function validate(string $value): void
    {
        $validTypes = [self::STORAGE, self::BASE64, self::STREAM];
        
        if (!in_array($value, $validTypes)) {
            throw new InvalidFormatException(
                "Invalid output type: {$value}. Valid types are: " . implode(', ', $validTypes)
            );
        }
    }

    public static function storage(): self
    {
        return new self(self::STORAGE);
    }

    public static function base64(): self
    {
        return new self(self::BASE64);
    }

    public static function stream(): self
    {
        return new self(self::STREAM);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isStorage(): bool
    {
        return $this->value === self::STORAGE;
    }

    public function isBase64(): bool
    {
        return $this->value === self::BASE64;
    }

    public function isStream(): bool
    {
        return $this->value === self::STREAM;
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