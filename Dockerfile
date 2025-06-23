# Usar una imagen base de PHP (ajusta la versión si usas otra, ej: 8.1)
FROM php:8.4-cli

# Instalar extensiones de PHP necesarias para SwordPHP y Workerman
RUN docker-php-ext-install pcntl sockets pdo pdo_mysql

# Instalar Composer para gestionar dependencias
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Establecer el directorio de trabajo principal del contenedor
WORKDIR /usr/src/app

# Copiar TODO el proyecto (swordCore y swordContent) al contenedor
COPY . .

# Ejecutar composer install DENTRO de la carpeta swordCore
RUN composer install --working-dir=./swordCore --no-dev --optimize-autoloader --prefer-dist

# Exponer el puerto que usa Workerman
EXPOSE 8787

# El comando para iniciar la aplicación, especificando la ruta a start.php
CMD ["php", "swordCore/start.php"]
