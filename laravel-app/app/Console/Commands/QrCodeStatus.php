<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\QrCode\Services\QrCodeGeneratorServiceInterface;

class QrCodeStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qrcode:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check QR Code generator status and dependencies';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('QR Code Generator Status');
        $this->info('========================');

        // Check if endroid/qr-code library is available
        $libraryAvailable = class_exists('Endroid\QrCode\Builder\Builder');
        
        if ($libraryAvailable) {
            $this->info('✅ endroid/qr-code library: AVAILABLE');
            $this->info('   Using LibraryQrCodeGenerator for production-quality QR codes');
        } else {
            $this->warn('⚠️  endroid/qr-code library: NOT AVAILABLE');
            $this->warn('   Using SimpleQrCodeGenerator (placeholder images only)');
            $this->newLine();
            $this->comment('To install the library, run:');
            $this->comment('  composer install');
        }

        // Check Redis availability
        $redisAvailable = class_exists('Redis') || class_exists('Predis\Client');
        
        if ($redisAvailable) {
            $this->info('✅ Redis support: AVAILABLE');
        } else {
            $this->warn('⚠️  Redis support: NOT AVAILABLE');
            $this->comment('Using array cache (not persistent across requests)');
        }

        // Get current generator instance
        try {
            $generator = app(QrCodeGeneratorServiceInterface::class);
            $generatorClass = get_class($generator);
            $this->newLine();
            $this->info("Current generator: {$generatorClass}");
        } catch (\Exception $e) {
            $this->error('Failed to resolve QR code generator: ' . $e->getMessage());
        }

        $this->newLine();
        
        if (!$libraryAvailable) {
            $this->comment('Note: The system is currently using placeholder QR code generation.');
            $this->comment('Install composer dependencies to enable real QR code generation.');
            return 1;
        }

        $this->info('✅ QR Code service is ready for production use');
        return 0;
    }
}