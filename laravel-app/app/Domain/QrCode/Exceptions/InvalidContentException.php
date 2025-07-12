<?php

namespace App\Domain\QrCode\Exceptions;

use Exception;

class InvalidContentException extends Exception
{
    public function __construct(string $message = 'Invalid QR code content', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}