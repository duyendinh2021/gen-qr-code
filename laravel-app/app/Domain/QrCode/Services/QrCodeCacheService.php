<?php

namespace App\Domain\QrCode\Services;

use App\Domain\QrCode\Entities\QrCode;
use App\Domain\QrCode\Repositories\QrCodeRepositoryInterface;
use App\Domain\QrCode\Events\QrCodeCached;

class QrCodeCacheService
{
    public function __construct(
        private QrCodeRepositoryInterface $repository
    ) {}

    public function get(string $cacheKey): ?QrCode
    {
        return $this->repository->findByCache($cacheKey);
    }

    public function store(QrCode $qrCode, int $ttlSeconds = 3600): void
    {
        $this->repository->cache($qrCode, $ttlSeconds);
        $qrCode->markAsCached();
        
        // Dispatch event
        event(new QrCodeCached($qrCode, $qrCode->getCacheKey()));
    }

    public function clear(string $cacheKey): void
    {
        $this->repository->clearCache($cacheKey);
    }

    public function getStats(): array
    {
        return $this->repository->getCacheStats();
    }

    public function generateCacheKey(string $content, string $configurationHash): string
    {
        return 'qr_code:' . md5($content . $configurationHash);
    }
}