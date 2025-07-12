<?php

namespace App\Domain\QrCode\Events;

use App\Domain\QrCode\Entities\QrCode;

class QrCodeCached
{
    public function __construct(
        private QrCode $qrCode,
        private string $cacheKey
    ) {}

    public function getQrCode(): QrCode
    {
        return $this->qrCode;
    }

    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }
}