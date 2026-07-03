# -------------------------------
# Stage 1: Builder
# -------------------------------
FROM php:8.2-cli AS builder

# Instalamos todo lo necesario para compilar y preparar el proyecto
RUN apt-get update && apt-get install -y \
    unzip git libzip-dev libpng-dev libonig-dev libxml2-dev libpq-dev \
    libcurl4-openssl-dev libssl-dev libfreetype6-dev libjpeg62-turbo-dev \
    libicu-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    pdo pdo_pgsql zip soap gd bcmath intl curl xml mbstring fileinfo exif

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Instalación de dependencias
RUN composer install --no-dev --optimize-autoloader --no-interaction

# -------------------------------
# Stage 2: Production
# -------------------------------
FROM php:8.2-apache

# REQUISITO CLAVE: Reinstalar extensiones necesarias en la imagen final
# SOAP requiere libxml2-dev y PDO_PGSQL requiere libpq-dev
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libxml2-dev \
    libpng-dev \
    libzip-dev \
    libicu-dev \
 && docker-php-ext-install \
    pdo pdo_pgsql soap gd bcmath intl zip \
 && rm -rf /var/lib/apt/lists/*

# Copiar el proyecto preparado desde el builder
COPY --from=builder /app /var/www/html

# Configuración de Apache, directorios y permisos
RUN a2enmod rewrite \
 && sed -i "s#/var/www/html#/var/www/html/public#g" /etc/apache2/sites-available/000-default.conf \
 && chown -R www-data:www-data /var/www/html \
 && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

WORKDIR /var/www/html

EXPOSE 80

# EJECUCIÓN:
# 1. No usamos 'USER www-data' antes del CMD porque Apache necesita ser root para abrir el puerto 80.
# 2. Ejecutamos la migración como www-data usando 'su' para evitar problemas de permisos en archivos generados.
#CMD bash -c "su -s /bin/bash -c 'php artisan migrate --force' www-data && apache2-foreground"
CMD bash -c "\
php artisan config:clear && \
php artisan route:clear && \
php artisan view:clear && \
echo 'Running migrations...' && \
php artisan migrate --seed --force && \
echo 'Starting Apache...' && \
apache2-foreground"

#Si no quiero ejecutar las migraciones automáticamente, simplemente dejo el CMD como:
# CMD apache2-foreground