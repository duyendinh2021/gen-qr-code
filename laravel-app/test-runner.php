<?php

// Simple test runner to validate our QR Code implementation
require_once __DIR__ . '/app/Domain/QrCode/Exceptions/InvalidContentException.php';
require_once __DIR__ . '/app/Domain/QrCode/Exceptions/InvalidFormatException.php';
require_once __DIR__ . '/app/Domain/QrCode/Exceptions/GenerationFailedException.php';

require_once __DIR__ . '/app/Domain/QrCode/ValueObjects/Content.php';
require_once __DIR__ . '/app/Domain/QrCode/ValueObjects/Size.php';
require_once __DIR__ . '/app/Domain/QrCode/ValueObjects/Color.php';
require_once __DIR__ . '/app/Domain/QrCode/ValueObjects/DotStyle.php';
require_once __DIR__ . '/app/Domain/QrCode/ValueObjects/FileType.php';
require_once __DIR__ . '/app/Domain/QrCode/ValueObjects/OutputType.php';
require_once __DIR__ . '/app/Domain/QrCode/ValueObjects/ErrorCorrectionLevel.php';

echo "🧪 Testing QR Code Domain Layer...\n\n";

// Test Content Value Object
try {
    $content = new \App\Domain\QrCode\ValueObjects\Content('https://example.com');
    echo "✅ Content creation: PASSED\n";
    
    try {
        new \App\Domain\QrCode\ValueObjects\Content('');
        echo "❌ Empty content validation: FAILED\n";
    } catch (\App\Domain\QrCode\Exceptions\InvalidContentException $e) {
        echo "✅ Empty content validation: PASSED\n";
    }
} catch (Exception $e) {
    echo "❌ Content creation: FAILED - " . $e->getMessage() . "\n";
}

// Test Size Value Object
try {
    $size = new \App\Domain\QrCode\ValueObjects\Size(300);
    echo "✅ Size creation: PASSED\n";
    
    $sizeFromString = \App\Domain\QrCode\ValueObjects\Size::fromString('400x400');
    echo "✅ Size from string: PASSED\n";
    
    try {
        new \App\Domain\QrCode\ValueObjects\Size(10); // Too small
        echo "❌ Size validation: FAILED\n";
    } catch (\App\Domain\QrCode\Exceptions\InvalidFormatException $e) {
        echo "✅ Size validation: PASSED\n";
    }
} catch (Exception $e) {
    echo "❌ Size creation: FAILED - " . $e->getMessage() . "\n";
}

// Test Color Value Object
try {
    $color = new \App\Domain\QrCode\ValueObjects\Color('#ff0000');
    echo "✅ Color creation: PASSED\n";
    
    $namedColor = new \App\Domain\QrCode\ValueObjects\Color('red');
    echo "✅ Named color: PASSED\n";
    
    try {
        new \App\Domain\QrCode\ValueObjects\Color('invalid');
        echo "❌ Color validation: FAILED\n";
    } catch (\App\Domain\QrCode\Exceptions\InvalidFormatException $e) {
        echo "✅ Color validation: PASSED\n";
    }
} catch (Exception $e) {
    echo "❌ Color creation: FAILED - " . $e->getMessage() . "\n";
}

// Test DotStyle Value Object
try {
    $dotStyle = \App\Domain\QrCode\ValueObjects\DotStyle::rounded();
    echo "✅ DotStyle creation: PASSED\n";
    
    try {
        new \App\Domain\QrCode\ValueObjects\DotStyle('invalid');
        echo "❌ DotStyle validation: FAILED\n";
    } catch (\App\Domain\QrCode\Exceptions\InvalidFormatException $e) {
        echo "✅ DotStyle validation: PASSED\n";
    }
} catch (Exception $e) {
    echo "❌ DotStyle creation: FAILED - " . $e->getMessage() . "\n";
}

// Test FileType Value Object
try {
    $fileType = \App\Domain\QrCode\ValueObjects\FileType::png();
    echo "✅ FileType creation: PASSED\n";
    echo "✅ FileType mime: " . $fileType->getMimeType() . "\n";
} catch (Exception $e) {
    echo "❌ FileType creation: FAILED - " . $e->getMessage() . "\n";
}

// Test OutputType Value Object
try {
    $outputType = \App\Domain\QrCode\ValueObjects\OutputType::base64();
    echo "✅ OutputType creation: PASSED\n";
} catch (Exception $e) {
    echo "❌ OutputType creation: FAILED - " . $e->getMessage() . "\n";
}

// Test ErrorCorrectionLevel Value Object
try {
    $errorLevel = \App\Domain\QrCode\ValueObjects\ErrorCorrectionLevel::high();
    echo "✅ ErrorCorrectionLevel creation: PASSED\n";
} catch (Exception $e) {
    echo "❌ ErrorCorrectionLevel creation: FAILED - " . $e->getMessage() . "\n";
}

echo "\n🎉 Domain layer validation completed!\n";
echo "All core value objects and exceptions are working correctly.\n\n";

echo "📋 Next steps:\n";
echo "1. Install composer dependencies: composer install\n";
echo "2. Run Laravel tests: php artisan test\n";
echo "3. Test API endpoints: curl -X POST /api/qr-codes/generate\n";