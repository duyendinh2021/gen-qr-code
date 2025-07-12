<?php

namespace App\Domain\QrCode\Exceptions;

use Exception;

class InvalidFormatException extends Exception
{
    public function __construct(string $message = 'Invalid format', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}