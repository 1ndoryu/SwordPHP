# Usar una imagen base de PHP
FROM php:8.2-cli

# 1. Instalar dependencias del sistema:
#    - git y unzip (para composer)
#    - libzip-dev (para la extensión 'zip')
#    - libpq-dev (para la extensión de PostgreSQL)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# 2. Instalar extensiones de PHP que vienen con el núcleo
RUN docker-php-ext-install pcntl sockets pdo pdo_mysql pdo_pgsql zip

# --- INICIO DE LA CORRECCIÓN FINAL ---
# 3. Instalar la extensión de Redis desde PECL y activarla
RUN pecl install redis && docker-php-ext-enable redis
# --- FIN DE LA CORRECCIÓN FINAL ---

# Instalar Composer para gestionar dependencias
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Establecer el directorio de trabajo principal del contenedor
WORKDIR /usr/src/app

# Copiar TODO el proyecto al contenedor
COPY . .

# Ejecutar composer install DENTRO de la carpeta swordCore
RUN composer install --working-dir=./swordCore --no-dev --optimize-autoloader --prefer-dist

# Exponer el puerto que usa Workerman
EXPOSE 8787

# El comando para iniciar la aplicación, pasando "start" como un argumento separado.
CMD ["php", "swordCore/start.php", "start"]