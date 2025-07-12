<?php

namespace App\Infrastructure\QrCode\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\QrCode\Repositories\QrCodeRepositoryInterface;
use App\Infrastructure\QrCode\Repositories\CacheQrCodeRepository;
use App\Domain\QrCode\Services\QrCodeGeneratorServiceInterface;
use App\Infrastructure\QrCode\Services\LibraryQrCodeGenerator;
use App\Domain\QrCode\Services\QrCodeCacheService;
use App\Infrastructure\QrCode\Services\FileStorageService;
use App\Application\QrCode\UseCases\GenerateQrCodeUseCase;

class QrCodeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository interface to implementation
        $this->app->bind(
            QrCodeRepositoryInterface::class,
            \App\Infrastructure\QrCode\Repositories\SimpleCacheQrCodeRepository::class
        );

        // Bind generator service interface to implementation
        $this->app->bind(
            QrCodeGeneratorServiceInterface::class,
            \App\Infrastructure\QrCode\Services\SimpleQrCodeGenerator::class
        );

        // Register cache service
        $this->app->singleton(QrCodeCacheService::class, function ($app) {
            return new QrCodeCacheService(
                $app->make(QrCodeRepositoryInterface::class)
            );
        });

        // Register file storage service
        $this->app->singleton(FileStorageService::class);

        // Register use case
        $this->app->singleton(GenerateQrCodeUseCase::class, function ($app) {
            return new GenerateQrCodeUseCase(
                $app->make(QrCodeGeneratorServiceInterface::class),
                $app->make(QrCodeCacheService::class),
                $app->make(FileStorageService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}