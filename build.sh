#!/bin/bash

# Dependencies

composer install

# Empty Storage

find storage/app -type f ! -name '.gitignore' -exec rm -f {} \;
find storage/logs -type f ! -name '.gitignore' -exec rm -f {} \;

# Clear Cache

php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan clear-compiled

# Remove Various

rm build.zip

rm public/hot

find . -name ".DS_Store" -delete

# Zip Files

zip -r build.zip . \
    -x '.git/*' \
    -x '.idea/*' \
    -x '.run/*' \
    -x 'docker/*' \
    -x 'node_modules/*' \
    -x 'tests/*' \
    -x '.editorconfig' \
    -x '.gitattributes' \
    -x '.gitignore' \
    -x '.prettierignore' \
    -x '.package-lock.json' \
    -x '.env' \
    -x 'build.sh' \
    -x 'docker-compose.yml' \
    -x 'postcss.config.js' \
    -x 'tailwind.config.js' \
    -x 'vite.config.js' \
    -x '*.zip'
