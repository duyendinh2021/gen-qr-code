<?php

namespace App\Domain\QrCode\Events;

use App\Domain\QrCode\Entities\QrCode;

class QrCodeGenerated
{
    public function __construct(
        private QrCode $qrCode,
        private float $generationTimeMs
    ) {}

    public function getQrCode(): QrCode
    {
        return $this->qrCode;
    }

    public function getGenerationTimeMs(): float
    {
        return $this->generationTimeMs;
    }
}