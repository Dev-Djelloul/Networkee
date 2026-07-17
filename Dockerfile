FROM php:8.2-fpm-alpine

# Installer Nginx et PostgreSQL
RUN apk add --no-cache \
    nginx \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql pdo

# Copier le projet
COPY . /var/www/html/

# Copier configuration Nginx
COPY nginx.conf /etc/nginx/nginx.conf

# Config PHP : limites d'upload
COPY uploads.ini "$PHP_INI_DIR/conf.d/uploads.ini"

# Dossiers temp/log Nginx accessibles en écriture par le worker (www-data)
RUN mkdir -p /var/cache/nginx/client_temp \
             /var/cache/nginx/fastcgi_temp \
             /var/cache/nginx/proxy_temp \
             /var/log/nginx \
    && chown -R www-data:www-data /var/cache/nginx /var/log/nginx

# Permissions du projet (uploads/ doit être inscriptible par PHP-FPM)
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 8080

# Au démarrage : rendre le volume uploads/ inscriptible (monté par Railway avant le CMD),
# initialiser la base, puis lancer PHP-FPM + Nginx.
CMD ["sh", "-c", "mkdir -p /var/www/html/uploads && chown -R www-data:www-data /var/www/html/uploads && chmod 775 /var/www/html/uploads && php /var/www/html/database/init.php; php-fpm -D && nginx -g 'daemon off;'"]
