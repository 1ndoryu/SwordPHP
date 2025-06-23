# Usar una imagen base de PHP (ajusta la versión si usas otra)
FROM php:8.2-cli

# --- INICIO DE LA CORRECCIÓN ---
# 1. Instalar dependencias del sistema: git y unzip, que son necesarios para Composer.
#    'apt-get update' actualiza la lista de paquetes disponibles.
#    '-y' responde "sí" automáticamente a cualquier pregunta.
#    'rm -rf /var/lib/apt/lists/*' limpia la caché para mantener la imagen pequeña.
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# 2. Instalar extensiones de PHP (añadimos 'zip' que también ayuda a Composer).
RUN docker-php-ext-install pcntl sockets pdo pdo_mysql zip
# --- FIN DE LA CORRECCIÓN ---

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
