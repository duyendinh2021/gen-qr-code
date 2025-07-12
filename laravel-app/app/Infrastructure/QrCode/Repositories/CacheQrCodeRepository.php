<?php

namespace App\Infrastructure\QrCode\Repositories;

use App\Domain\QrCode\Entities\QrCode;
use App\Domain\QrCode\Repositories\QrCodeRepositoryInterface;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class CacheQrCodeRepository implements QrCodeRepositoryInterface
{
    private const DEFAULT_TTL = 3600; // 1 hour

    public function save(QrCode $qrCode): void
    {
        // For this implementation, we'll store QR codes in cache only
        // In a production environment, you might also store to a database
        $this->cache($qrCode);
    }

    public function findByCache(string $cacheKey): ?QrCode
    {
        try {
            $cached = Redis::get($cacheKey);
            
            if ($cached) {
                $data = json_decode($cached, true);
                // For simplicity, we'll create a basic QrCode object
                // In production, you'd want proper serialization/deserialization
                return $this->deserializeQrCode($data);
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to retrieve QR code from cache', [
                'cache_key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function cache(QrCode $qrCode, int $ttlSeconds = self::DEFAULT_TTL): void
    {
        try {
            $data = $this->serializeQrCode($qrCode);
            Redis::setex($qrCode->getCacheKey(), $ttlSeconds, json_encode($data));
            
            // Store cache metadata
            $this->storeCacheMetadata($qrCode->getCacheKey(), $ttlSeconds);
            
            Log::info('QR code cached successfully', [
                'cache_key' => $qrCode->getCacheKey(),
                'ttl' => $ttlSeconds
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to cache QR code', [
                'cache_key' => $qrCode->getCacheKey(),
                'error' => $e->getMessage()
            ]);
        }
    }

    public function clearCache(string $cacheKey): void
    {
        try {
            Redis::del($cacheKey);
            $this->removeCacheMetadata($cacheKey);
            
            Log::info('QR code cache cleared', ['cache_key' => $cacheKey]);
        } catch (\Exception $e) {
            Log::error('Failed to clear QR code cache', [
                'cache_key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getCacheStats(): array
    {
        try {
            $pattern = 'qr_code:*';
            $keys = Redis::keys($pattern);
            $metadataKeys = Redis::keys('qr_cache_meta:*');
            
            return [
                'total_cached' => count($keys),
                'cache_size_bytes' => $this->calculateCacheSize($keys),
                'metadata_entries' => count($metadataKeys),
                'cache_hit_rate' => $this->getCacheHitRate(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get cache stats', ['error' => $e->getMessage()]);
            return [
                'total_cached' => 0,
                'cache_size_bytes' => 0,
                'metadata_entries' => 0,
                'cache_hit_rate' => 0,
            ];
        }
    }

    private function serializeQrCode(QrCode $qrCode): array
    {
        return [
            'id' => $qrCode->getId(),
            'content' => $qrCode->getContent()->getValue(),
            'data' => $qrCode->getData(),
            'file_path' => $qrCode->getFilePath(),
            'created_at' => $qrCode->getCreatedAt()->toISOString(),
            'cached_at' => $qrCode->getCachedAt()?->toISOString(),
            'configuration' => [
                'size' => $qrCode->getConfiguration()->getSize()->__toString(),
                'dot_style' => $qrCode->getConfiguration()->getDotStyle()->getValue(),
                'color' => $qrCode->getConfiguration()->getColor()->getValue(),
                'background' => $qrCode->getConfiguration()->getBackground()->getValue(),
                'file_type' => $qrCode->getConfiguration()->getFileType()->getValue(),
                'output_type' => $qrCode->getConfiguration()->getOutputType()->getValue(),
                'error_correction' => $qrCode->getConfiguration()->getErrorCorrectionLevel()->getValue(),
            ],
        ];
    }

    private function deserializeQrCode(array $data): QrCode
    {
        // This is a simplified deserialization
        // In production, you'd want more robust handling
        return new QrCode(
            new \App\Domain\QrCode\ValueObjects\Content($data['content']),
            \App\Domain\QrCode\Entities\QrCodeConfiguration::default(), // Simplified
            $data['id']
        );
    }

    private function storeCacheMetadata(string $cacheKey, int $ttl): void
    {
        $metaKey = 'qr_cache_meta:' . $cacheKey;
        $metadata = [
            'cached_at' => now()->toISOString(),
            'ttl' => $ttl,
            'hits' => 0,
        ];
        Redis::setex($metaKey, $ttl, json_encode($metadata));
    }

    private function removeCacheMetadata(string $cacheKey): void
    {
        $metaKey = 'qr_cache_meta:' . $cacheKey;
        Redis::del($metaKey);
    }

    private function calculateCacheSize(array $keys): int
    {
        $totalSize = 0;
        foreach ($keys as $key) {
            $value = Redis::get($key);
            if ($value) {
                $totalSize += strlen($value);
            }
        }
        return $totalSize;
    }

    private function getCacheHitRate(): float
    {
        // Simplified implementation
        // In production, you'd track hits/misses more accurately
        return 0.85; // 85% hit rate as example
    }
}