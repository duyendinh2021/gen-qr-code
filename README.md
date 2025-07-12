
## 🚀 Quick Start

### 1. Setup Project
```bash
# Clone hoặc tạo folder project
mkdir my-laravel-app
cd my-laravel-app

# Copy docker-compose.yml và các config files
# Tạo các folder cần thiết
mkdir -p docker/nginx docker/php docker/mysql
```

### 2. Start Development Environment
```bash
# Start all services
docker-compose up -d

# Check running containers
docker-compose ps

# View logs
docker-compose logs -f
```

### 3. Install Laravel (nếu chưa có)
```bash
# Run composer to create new Laravel project
docker-compose run --rm composer create-project laravel/laravel .

# Set permissions
sudo chown -R $USER:$USER .
chmod -R 775 storage bootstrap/cache
```

### 4. Configure Laravel
```bash
# Copy environment file
cp .env.example .env

# Generate app key
docker exec -it dev_php php artisan key:generate

# Run migrations
docker exec -it dev_php php artisan migrate
```

## 🌐 Access URLs

- **Web Application**: http://localhost:8080
- **MySQL**: localhost:3306 (user: laravel, pass: laravel123)
- **Redis**: localhost:6379
- **Vite Dev Server**: http://localhost:5173

## 🛠️ Development Commands

### Using Makefile (recommended)
```bash
make up          # Start services
make down        # Stop services  
make logs        # View logs
make shell       # Access PHP container
make composer install    # Run composer install
make npm install # Run npm install
make fresh       # Fresh Laravel installation
make test        # Run tests
make clear       # Clear all caches
```

### Using Docker Compose directly
```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# Access PHP container
docker exec -it dev_php bash

# Run Artisan commands
docker exec -it dev_php php artisan migrate

# Run Composer
docker-compose run --rm composer install

# Run NPM
docker-compose run --rm node npm install
```

## 📁 Project Structure
```
my-laravel-app/
├── docker-compose.yml
├── Makefile
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   ├── php/
│   │   └── php.ini
│   └── mysql/
│       └── my.cnf
├── app/
├── public/
└── ... (Laravel files)
```

## 🔧 Customization

### Change PHP version
```yaml
# In docker-compose.yml
php:
  image: php:8.3-fpm  # Change to desired version
```

### Add more PHP extensions
```yaml
# In docker-compose.yml, modify the command section
command: >
  bash -c "
  apt-get update && 
  apt-get install -y libcurl4-openssl-dev && 
  docker-php-ext-install curl && 
  php-fpm"
```

### Change ports
```yaml
# In docker-compose.yml
nginx:
  ports:
    - "80:80"     # Change from 8080 to 80
    - "443:443"   # Change from 8443 to 443
```

## 🐛 Troubleshooting

### Permission issues
```bash
# Fix Laravel permissions
sudo chown -R $USER:$USER .
chmod -R 775 storage bootstrap/cache
```

### Container won't start
```bash
# Check logs
docker-compose logs php
docker-compose logs nginx

# Rebuild containers
docker-compose down
docker-compose up --build -d
```

### Database connection issues
```bash
# Check if MySQL is running
docker-compose ps

# Test connection
docker exec -it dev_mysql mysql -u laravel -p laravel_dev
```