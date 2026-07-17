FROM php:8.2-fpm-alpine

# Installer Nginx et PostgreSQL
RUN apk add --no-cache \
    nginx \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql pdo

# Copier le projet
COPY . /var/www/html/

# Créer configuration Nginx
RUN mkdir -p /etc/nginx/conf.d
COPY nginx.conf /etc/nginx/nginx.conf

# Permissions
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Script de démarrage
RUN echo '#!/bin/sh\nphp-fpm -D\nnginx -g "daemon off;"' > /start.sh && chmod +x /start.sh

EXPOSE 8080

CMD ["/start.sh"]
