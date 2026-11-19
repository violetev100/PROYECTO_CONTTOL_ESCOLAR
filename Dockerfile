# Control Escolar | PHP 8.3.36 + Apache
# Ubicación: raíz del proyecto (junto a docker-compose.yml)
FROM php:8.3.36-apache

# Extensión que usa la app (PDO + MariaDB) y nombre de servidor para evitar
# el aviso "Could not reliably determine the server's fully qualified domain name"
RUN docker-php-ext-install pdo_mysql \
 && echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
 && a2enconf servername

# Código de la app dentro de la imagen. En desarrollo, el volumen de
# docker-compose.yml (./app:/var/www/html) lo sustituye para ver los cambios
# al instante sin reconstruir.
COPY --chown=www-data:www-data app/ /var/www/html/

EXPOSE 80
