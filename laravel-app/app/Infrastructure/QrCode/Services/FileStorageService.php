<?php

namespace App\Infrastructure\QrCode\Services;

use App\Domain\QrCode\Entities\QrCode;
use App\Domain\QrCode\ValueObjects\OutputType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FileStorageService
{
    private const STORAGE_DISK = 'public';
    private const QR_CODE_PATH = 'qr-codes';

    public function store(QrCode $qrCode): string
    {
        try {
            $fileName = $qrCode->getFileName();
            $filePath = self::QR_CODE_PATH . '/' . $fileName;
            
            Storage::disk(self::STORAGE_DISK)->put($filePath, $qrCode->getData());
            
            $fullPath = Storage::disk(self::STORAGE_DISK)->path($filePath);
            $qrCode->setFilePath($fullPath);
            
            Log::info('QR code file stored', [
                'file_path' => $fullPath,
                'file_name' => $fileName,
                'qr_code_id' => $qrCode->getId()
            ]);
            
            return $fullPath;
        } catch (\Exception $e) {
            Log::error('Failed to store QR code file', [
                'qr_code_id' => $qrCode->getId(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getUrl(QrCode $qrCode): string
    {
        if (!$qrCode->getFilePath()) {
            throw new \RuntimeException('QR code file path not set');
        }

        $fileName = $qrCode->getFileName();
        $filePath = self::QR_CODE_PATH . '/' . $fileName;
        
        return Storage::disk(self::STORAGE_DISK)->url($filePath);
    }

    public function delete(QrCode $qrCode): bool
    {
        try {
            if (!$qrCode->getFilePath()) {
                return true; // Nothing to delete
            }

            $fileName = $qrCode->getFileName();
            $filePath = self::QR_CODE_PATH . '/' . $fileName;
            
            $deleted = Storage::disk(self::STORAGE_DISK)->delete($filePath);
            
            if ($deleted) {
                Log::info('QR code file deleted', [
                    'file_path' => $filePath,
                    'qr_code_id' => $qrCode->getId()
                ]);
            }
            
            return $deleted;
        } catch (\Exception $e) {
            Log::error('Failed to delete QR code file', [
                'qr_code_id' => $qrCode->getId(),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function exists(QrCode $qrCode): bool
    {
        if (!$qrCode->getFilePath()) {
            return false;
        }

        $fileName = $qrCode->getFileName();
        $filePath = self::QR_CODE_PATH . '/' . $fileName;
        
        return Storage::disk(self::STORAGE_DISK)->exists($filePath);
    }

    public function getStorageStats(): array
    {
        try {
            $files = Storage::disk(self::STORAGE_DISK)->files(self::QR_CODE_PATH);
            $totalSize = 0;
            
            foreach ($files as $file) {
                $totalSize += Storage::disk(self::STORAGE_DISK)->size($file);
            }
            
            return [
                'total_files' => count($files),
                'total_size_bytes' => $totalSize,
                'total_size_mb' => round($totalSize / 1024 / 1024, 2),
                'storage_path' => Storage::disk(self::STORAGE_DISK)->path(self::QR_CODE_PATH),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get storage stats', ['error' => $e->getMessage()]);
            return [
                'total_files' => 0,
                'total_size_bytes' => 0,
                'total_size_mb' => 0,
                'storage_path' => 'Unknown',
            ];
        }
    }

    public function cleanup(int $daysOld = 30): int
    {
        try {
            $files = Storage::disk(self::STORAGE_DISK)->files(self::QR_CODE_PATH);
            $deletedCount = 0;
            $cutoffTime = now()->subDays($daysOld);
            
            foreach ($files as $file) {
                $lastModified = Storage::disk(self::STORAGE_DISK)->lastModified($file);
                
                if ($lastModified < $cutoffTime->timestamp) {
                    if (Storage::disk(self::STORAGE_DISK)->delete($file)) {
                        $deletedCount++;
                    }
                }
            }
            
            Log::info('QR code file cleanup completed', [
                'deleted_count' => $deletedCount,
                'cutoff_days' => $daysOld
            ]);
            
            return $deletedCount;
        } catch (\Exception $e) {
            Log::error('Failed to cleanup QR code files', ['error' => $e->getMessage()]);
            return 0;
        }
    }
}