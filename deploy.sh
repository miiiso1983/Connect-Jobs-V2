#!/bin/bash

# Connect Jobs V2 - Cloudways Deployment Script
# Usage: ./deploy.sh

echo "🚀 Starting Connect Jobs V2 deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "artisan file not found. Please run this script from the Laravel root directory."
    exit 1
fi

print_status "Setting up file permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

print_status "Installing Composer dependencies..."
if command -v composer &> /dev/null; then
    composer install --optimize-autoloader --no-dev --no-interaction
    print_success "Composer dependencies installed"
else
    print_error "Composer not found. Please install Composer first."
    exit 1
fi

print_status "Installing Node.js dependencies..."
if command -v npm &> /dev/null; then
    npm install
    print_success "Node.js dependencies installed"
else
    print_warning "npm not found. Skipping Node.js dependencies."
fi

print_status "Building assets..."
if command -v npm &> /dev/null; then
    npm run build
    print_success "Assets built successfully"
else
    print_warning "Skipping asset build (npm not available)"
fi

# Check if .env exists
if [ ! -f ".env" ]; then
    print_status "Creating .env file from .env.example..."
    cp .env.example .env
    print_warning "Please edit .env file with your database and mail settings"
fi

print_status "Generating application key..."
php artisan key:generate --force

print_status "Running database migrations..."
php artisan migrate --force
print_success "Database migrations completed"

print_status "Seeding database..."
php artisan db:seed --force
print_success "Database seeded"

print_status "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
print_success "Application optimized"

print_status "Creating storage link..."
php artisan storage:link

print_success "🎉 Deployment completed successfully!"
echo ""
echo "📋 Next steps:"
echo "1. Configure your web server to point to the public/ directory"
echo "2. Set up SSL certificate"
echo "3. Configure cron job: * * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1"
echo "4. Update .env file with production settings"
echo ""
echo "🔐 Default admin credentials:"
echo "Email: admin@connectjobs.com"
echo "Password: password123"
echo ""
print_warning "⚠️  Remember to change the admin password after first login!"
