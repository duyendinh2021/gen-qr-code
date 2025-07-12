<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Infrastructure\QrCode\Services\FileStorageService;
use App\Domain\QrCode\Services\QrCodeCacheService;

class QrCodeCleanupCommand extends Command
{
    protected $signature = 'qrcode:cleanup {--days=30 : Number of days to keep files}';
    protected $description = 'Clean up old QR code files and cache entries';

    public function __construct(
        private FileStorageService $storageService,
        private QrCodeCacheService $cacheService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $days = (int) $this->option('days');
        
        $this->info("Starting QR code cleanup (files older than {$days} days)...");
        
        // Clean up files
        $deletedFiles = $this->storageService->cleanup($days);
        $this->info("Deleted {$deletedFiles} old QR code files");
        
        // Get storage stats
        $stats = $this->storageService->getStorageStats();
        $this->info("Remaining files: {$stats['total_files']}");
        $this->info("Storage used: {$stats['total_size_mb']} MB");
        
        // Get cache stats
        $cacheStats = $this->cacheService->getStats();
        $this->info("Cache stats:");
        $this->line("  - Driver: " . ($cacheStats['cache_driver'] ?? 'unknown'));
        $this->line("  - Hit rate: " . ($cacheStats['cache_hit_rate'] * 100) . "%");
        
        $this->info('QR code cleanup completed successfully!');
        
        return Command::SUCCESS;
    }
}