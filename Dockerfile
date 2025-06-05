# Base image stage
FROM dunglas/frankenphp:latest AS base

ENV OS_LOCALE=en_US.UTF-8

# Locale setup
RUN apt-get update && apt-get install -y locales && \
    locale-gen $OS_LOCALE && \
    echo "LANG=$OS_LOCALE" > /etc/default/locale

ENV DEBIAN_FRONTEND=noninteractive \
    LC_ALL=$OS_LOCALE \
    LANG=$OS_LOCALE \
    LANGUAGE=$OS_LOCALE

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libcurl4-openssl-dev && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN install-php-extensions \
    pdo_sqlite \
    sqlite3 \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    redis \
    zip \
    intl \
    opcache \
    curl \
    http \
    json \
    openssl \
    tokenizer \
    xml \
    ctype \
    fileinfo

# Copy Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.lock ./

# Development Stage
FROM base AS development

COPY . .

# Install dependencies
RUN composer install --no-scripts && \
    composer dump-autoload --optimize

# Prepare environment
RUN cp .env.example .env || true && \
    touch database/database.sqlite && \
    php artisan key:generate && \
    php artisan migrate:fresh --seed --force && \
    php artisan passport:keys --force && \
    php artisan passport:client --personal --name="MyApp Personal Access Client" --no-interaction

# Set permissions
RUN chown -R www-data:www-data /app && \
    chmod -R 755 /app && \
    chmod -R 775 /app/storage /app/bootstrap/cache

# Expose ports
EXPOSE 80 443

# Copy Caddyfile ke lokasi default
COPY Caddyfile /etc/caddy/Caddyfile

# Jalankan FrankenPHP (menggantikan Caddy)
#CMD ["frankenphp", "-c", "/etc/caddy/Caddyfile"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]


# docker run -p 8000:80 laravel-backend-api frankenphp run

