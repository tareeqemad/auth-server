#!/usr/bin/env bash
# Production deploy script for GEDCO SSO
# Run from the project root on the server:  bash deploy.sh

set -e

echo ">> 1. Composer install (production)"
composer install --no-dev --prefer-dist --optimize-autoloader

echo ">> 2. Build frontend assets"
npm ci
npm run build

echo ">> 3. Generate APP_KEY if missing"
if ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

echo ">> 4. Set production permissions"
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
# Secure the .env
chmod 600 .env

echo ">> 5. Run migrations"
php artisan migrate --force

echo ">> 6. Passport keys (only if missing)"
if [ ! -f storage/oauth-private.key ]; then
    php artisan passport:keys
fi
chmod 600 storage/oauth-private.key storage/oauth-public.key

echo ">> 7. Storage symlink"
php artisan storage:link || true

echo ">> 8. Cache configs/routes/views (production)"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo ">> 9. Clear old cached data"
php artisan cache:clear

echo ">> Done. Remember to:"
echo "   - Update OAuth client redirect_uris to production domain"
echo "   - Review settings table (SMS credentials, branding)"
echo "   - Configure cron for scheduler if needed"
