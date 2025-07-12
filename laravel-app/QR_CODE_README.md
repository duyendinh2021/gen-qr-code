# QR Code Generator Service

A comprehensive QR Code Generator service built with Laravel following Domain-Driven Design (DDD) principles. The service supports multiple output formats, customization options, and high-performance caching.

## 🚀 Features

- **Multiple Formats**: PNG, JPG, SVG, PDF
- **Output Types**: Storage, Base64, Stream
- **Customization**: Size, colors, dot styles, error correction levels
- **High Performance**: Redis caching, 1000+ req/s target
- **DDD Architecture**: Clean, maintainable, and testable code
- **Smart Fallback**: Automatically uses best available QR generator

## 🔧 Installation & Setup

### 1. Install Dependencies
```bash
cd laravel-app
composer install
```

**Note**: If composer install fails due to firewall restrictions, the service will automatically use a fallback generator that creates placeholder QR code images. Install dependencies when possible for production-quality QR codes.

### 2. Check System Status
```bash
php artisan qrcode:status
```

This command will show:
- ✅ Library availability status
- ⚠️ Current generator being used
- 📋 Setup recommendations

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### Dependencies

The service requires these packages for full functionality:
- `endroid/qr-code`: For production-quality QR code generation
- `predis/predis`: For Redis caching support

**Fallback Behavior**: If dependencies are not available, the service automatically switches to:
- `SimpleQrCodeGenerator`: Creates placeholder QR code images
- Array caching instead of Redis

## 📋 API Endpoints

### Generate QR Code
```bash
POST /api/qr-codes/generate
```

**Request Body:**
```json
{
    "content": "https://example.com",
    "options": {
        "size": 300,
        "dot_style": "rounded",
        "color": "#000000",
        "background": "#ffffff",
        "file_type": "png",
        "output_type": "base64",
        "error_correction": "M"
    }
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "qr_code": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...",
        "format": "png",
        "size": "300x300",
        "cached": false,
        "generation_time_ms": 45
    }
}
```

### Get Statistics
```bash
GET /api/qr-codes/stats
```

### Health Check
```bash
GET /api/qr-codes/health
```

## 🏗️ Architecture

The project follows DDD (Domain-Driven Design) principles:

```
app/
├── Domain/QrCode/           # Core business logic
│   ├── Entities/            # Business entities
│   ├── ValueObjects/        # Immutable value objects
│   ├── Services/            # Domain services
│   ├── Repositories/        # Repository interfaces
│   ├── Exceptions/          # Domain exceptions
│   └── Events/              # Domain events
├── Infrastructure/QrCode/   # External integrations
│   ├── Repositories/        # Repository implementations
│   ├── Services/            # External service integrations
│   └── Providers/           # Service providers
└── Application/QrCode/      # Application layer
    ├── UseCases/            # Application use cases
    ├── DTOs/                # Data transfer objects
    └── Services/            # Application services
```

## 🔧 Configuration

### Environment Variables

```env
# QR Code Generator Configuration
QR_CACHE_TTL=3600
QR_MAX_CONTENT_LENGTH=4296
QR_DEFAULT_SIZE=300
QR_STORAGE_CLEANUP_DAYS=30

# Cache Configuration
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Storage Configuration
FILESYSTEM_DISK=public
```

## 📊 Supported Options

### File Types
- **PNG**: Portable Network Graphics
- **JPG/JPEG**: Joint Photographic Experts Group
- **SVG**: Scalable Vector Graphics
- **PDF**: Portable Document Format

### Output Types
- **storage**: Save to disk/cloud storage
- **base64**: Return base64 encoded string
- **stream**: Return file stream for download

### Dot Styles
- **square**: Traditional square dots
- **circle**: Circular dots
- **rounded**: Rounded square dots

### Error Correction Levels
- **L**: Low (~7% recovery)
- **M**: Medium (~15% recovery)
- **Q**: Quartile (~25% recovery)
- **H**: High (~30% recovery)

### Size Limits
- **Minimum**: 50x50 pixels
- **Maximum**: 2000x2000 pixels
- **Default**: 300x300 pixels

## 🚀 Installation & Setup

1. **Install Dependencies**
   ```bash
   composer install
   ```

2. **Configure Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Setup Storage**
   ```bash
   php artisan storage:link
   ```

4. **Run Tests**
   ```bash
   php artisan test
   ```

## 🧪 Testing

### Unit Tests
Test individual domain components:
```bash
php artisan test tests/Unit/Domain/QrCode/
```

### Feature Tests
Test API endpoints:
```bash
php artisan test tests/Feature/QrCode/
```

### All Tests
```bash
php artisan test
```

## ⚡ Performance

- **Target**: 1000 requests per second
- **Caching**: Redis-based caching with configurable TTL
- **Memory**: Optimized for low memory usage
- **Storage**: Automatic cleanup of old files

### Cache Strategy
- **Key Format**: `qr_code:{hash(content+config)}`
- **TTL**: Configurable (default: 1 hour)
- **Invalidation**: Manual or automatic expiration

## 📈 Monitoring

The service provides built-in monitoring capabilities:

- **Generation Time**: Track QR code generation performance
- **Cache Hit Rate**: Monitor caching effectiveness
- **Storage Usage**: Track disk space usage
- **Error Rates**: Monitor failed generations

## 🔒 Security

- **Input Validation**: Comprehensive request validation
- **Content Limits**: Maximum content length enforcement
- **File Type Validation**: Strict file type checking
- **Error Handling**: Secure error messages

## 📚 Examples

### Basic QR Code
```bash
curl -X POST http://localhost/api/qr-codes/generate \
  -H "Content-Type: application/json" \
  -d '{"content": "Hello World"}'
```

### Custom QR Code
```bash
curl -X POST http://localhost/api/qr-codes/generate \
  -H "Content-Type: application/json" \
  -d '{
    "content": "https://example.com",
    "options": {
      "size": 400,
      "dot_style": "rounded",
      "color": "#ff0000",
      "background": "#ffffff",
      "file_type": "svg",
      "output_type": "base64",
      "error_correction": "H"
    }
  }'
```

### File Storage
```bash
curl -X POST http://localhost/api/qr-codes/generate \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Store this QR code",
    "options": {
      "output_type": "storage",
      "file_type": "png"
    }
  }'
```

## 🔧 Troubleshooting

### Common Issues

1. **Cache Connection Error**
   - Check Redis connection
   - Verify REDIS_HOST and REDIS_PORT in .env

2. **Storage Permission Error**
   - Ensure storage/app/public is writable
   - Run `php artisan storage:link`

3. **Memory Limit Error**
   - Increase PHP memory_limit
   - Optimize QR code size

## 🤝 Contributing

1. Follow DDD principles
2. Write comprehensive tests
3. Document new features
4. Follow PSR-12 coding standards

## 📄 License

This project is licensed under the MIT License.