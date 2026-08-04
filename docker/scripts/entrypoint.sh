#!/bin/sh
set -e

mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

# Refresh Vite assets from the current image. This avoids an existing public
# named volume continuing to serve assets from an older deployment.
if [ -d /opt/app-public/build ]; then
    rm -rf public/build
    cp -R /opt/app-public/build public/build
fi

if [ ! -L public/storage ]; then
    rm -rf public/storage
    ln -s ../storage/app/public public/storage
fi

php artisan package:discover --ansi

if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache
    php artisan view:cache
fi

exec "$@"
