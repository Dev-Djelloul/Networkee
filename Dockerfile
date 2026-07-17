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

# Permissions
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 8080

CMD ["sh", "-c", "php /var/www/html/database/init.php; php-fpm -D && nginx -g 'daemon off;'"]
