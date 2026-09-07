#!/bin/bash

# Re-run inside the dev container unless we are already in it

if [ ! -f /.dockerenv ]; then
    stopped=$(comm -23 \
        <(docker compose config --services | sort) \
        <(docker compose ps --services --status running | sort))

    if [ -n "$stopped" ]; then
        echo "These containers are not running: $(echo $stopped | tr '\n' ' ')" >&2
        echo 'Start them with: docker compose up -d' >&2
        exit 1
    fi

    exec docker compose exec -T -u "$(id -u):$(id -g)" php-fpm ./build.sh "$@"
fi

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

rm clientverse-0.0.0.zip

rm public/hot

find . -name ".DS_Store" -delete

# Zip Files

zip -r clientverse-0.0.0.zip . \
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
    -x 'vite.config.js' \
    -x 'SPECS.md' \
    -x '*.zip'
