FROM php:8.2-apache

# Instalar soporte para MySQL (mysqli + PDO)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Activar mod_rewrite (opcional pero recomendado)
RUN a2enmod rewrite

# Copiar proyecto
COPY . /var/www/html/

# Permisos correctos
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80