#!/bin/sh
set -e

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "${GREEN}Starting OpenBiz Suite initialization...${NC}"

# Wait for MySQL to be ready
echo "${YELLOW}Waiting for MySQL to be ready...${NC}"
until nc -z -v -w30 mysql 3306; do
  echo "Waiting for MySQL connection..."
  sleep 2
done
echo "${GREEN}MySQL is ready!${NC}"

# Check if .env exists, if not copy from example
if [ ! -f .env ]; then
  echo "${YELLOW}Creating .env file from .env.example...${NC}"
  cp .env.example .env
  echo "${GREEN}.env file created!${NC}"
fi

# Install composer dependencies if vendor directory doesn't exist
if [ ! -d "vendor" ]; then
  echo "${YELLOW}Installing Composer dependencies...${NC}"
  composer install --no-interaction --prefer-dist --optimize-autoloader
  echo "${GREEN}Composer dependencies installed!${NC}"
else
  echo "${GREEN}Composer dependencies already installed.${NC}"
fi

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" .env; then
  echo "${YELLOW}Generating application key...${NC}"
  php artisan key:generate --no-interaction
  echo "${GREEN}Application key generated!${NC}"
else
  echo "${GREEN}Application key already exists.${NC}"
fi

# Run migrations
echo "${YELLOW}Running database migrations...${NC}"
php artisan migrate --force --no-interaction

# Check if database is empty (no users), then seed
USER_COUNT=$(php artisan tinker --execute="echo App\Models\User::count();" 2>/dev/null || echo "0")
if [ "$USER_COUNT" = "0" ]; then
  echo "${YELLOW}Seeding database...${NC}"
  php artisan db:seed --force --no-interaction
  echo "${GREEN}Database seeded!${NC}"
else
  echo "${GREEN}Database already seeded.${NC}"
fi

# Clear and cache config
echo "${YELLOW}Optimizing application...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "${GREEN}Application optimized!${NC}"

echo "${GREEN}Initialization complete! Starting PHP-FPM...${NC}"

# Execute the main container command
exec "$@"
