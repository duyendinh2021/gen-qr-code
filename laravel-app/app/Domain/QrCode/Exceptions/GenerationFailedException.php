<?php

namespace App\Domain\QrCode\Exceptions;

use Exception;

class GenerationFailedException extends Exception
{
    public function __construct(string $message = 'QR code generation failed', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}