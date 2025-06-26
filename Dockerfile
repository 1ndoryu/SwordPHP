FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*


RUN docker-php-ext-install pcntl sockets pdo pdo_mysql pdo_pgsql zip

RUN pecl install redis && docker-php-ext-enable redis

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /usr/src/app

COPY . .

RUN composer install --no-dev --optimize-autoloader --prefer-dist

EXPOSE 8787

CMD ["php", "start.php", "start"]