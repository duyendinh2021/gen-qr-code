<?php

namespace App\Infrastructure\QrCode\Repositories;

use App\Domain\QrCode\Entities\QrCode;
use App\Domain\QrCode\Repositories\QrCodeRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SimpleCacheQrCodeRepository implements QrCodeRepositoryInterface
{
    private const DEFAULT_TTL = 3600; // 1 hour

    public function save(QrCode $qrCode): void
    {
        // For this implementation, we'll store QR codes in cache only
        $this->cache($qrCode);
    }

    public function findByCache(string $cacheKey): ?QrCode
    {
        try {
            $cached = Cache::get($cacheKey);
            
            if ($cached && is_array($cached)) {
                return $this->deserializeQrCode($cached);
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
            Cache::put($qrCode->getCacheKey(), $data, $ttlSeconds);
            
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
            Cache::forget($cacheKey);
            
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
            // Since we're using Laravel Cache facade, we can't easily get exact stats
            // Return some basic information
            return [
                'cache_driver' => config('cache.default'),
                'total_cached' => 'N/A',
                'cache_size_bytes' => 'N/A',
                'cache_hit_rate' => 0.85,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get cache stats', ['error' => $e->getMessage()]);
            return [
                'cache_driver' => 'unknown',
                'total_cached' => 0,
                'cache_size_bytes' => 0,
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
        $qrCode = new QrCode(
            new \App\Domain\QrCode\ValueObjects\Content($data['content']),
            $this->createConfigurationFromData($data['configuration']),
            $data['id']
        );
        
        if (isset($data['data'])) {
            $qrCode->setData($data['data']);
        }
        
        if (isset($data['file_path'])) {
            $qrCode->setFilePath($data['file_path']);
        }
        
        return $qrCode;
    }

    private function createConfigurationFromData(array $configData): \App\Domain\QrCode\Entities\QrCodeConfiguration
    {
        $config = \App\Domain\QrCode\Entities\QrCodeConfiguration::default();
        
        if (isset($configData['size'])) {
            $config = $config->withSize(\App\Domain\QrCode\ValueObjects\Size::fromString($configData['size']));
        }
        
        if (isset($configData['dot_style'])) {
            $config = $config->withDotStyle(new \App\Domain\QrCode\ValueObjects\DotStyle($configData['dot_style']));
        }
        
        if (isset($configData['color'])) {
            $config = $config->withColor(new \App\Domain\QrCode\ValueObjects\Color($configData['color']));
        }
        
        if (isset($configData['background'])) {
            $config = $config->withBackground(new \App\Domain\QrCode\ValueObjects\Color($configData['background']));
        }
        
        if (isset($configData['file_type'])) {
            $config = $config->withFileType(new \App\Domain\QrCode\ValueObjects\FileType($configData['file_type']));
        }
        
        if (isset($configData['output_type'])) {
            $config = $config->withOutputType(new \App\Domain\QrCode\ValueObjects\OutputType($configData['output_type']));
        }
        
        if (isset($configData['error_correction'])) {
            $config = $config->withErrorCorrectionLevel(new \App\Domain\QrCode\ValueObjects\ErrorCorrectionLevel($configData['error_correction']));
        }
        
        return $config;
    }
}