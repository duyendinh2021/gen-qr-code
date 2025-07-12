<?php

namespace App\Domain\QrCode\Repositories;

use App\Domain\QrCode\Entities\QrCode;

interface QrCodeRepositoryInterface
{
    public function save(QrCode $qrCode): void;

    public function findByCache(string $cacheKey): ?QrCode;

    public function cache(QrCode $qrCode, int $ttlSeconds = 3600): void;

    public function clearCache(string $cacheKey): void;

    public function getCacheStats(): array;
}