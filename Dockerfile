FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    zip \
    libonig-dev \
    libicu-dev \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_mysql gd zip mbstring intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chmod -R 775 storage bootstrap/cache

RUN cp .env.example .env

RUN php artisan key:generate

RUN rm -rf node_modules package-lock.json

RUN npm install

EXPOSE 3000

CMD ["composer", "run", "dev"]
