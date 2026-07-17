FROM php:8.2-apache

# Installer PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql pdo \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Activer mod_rewrite
RUN a2enmod rewrite

# Copier le projet
COPY . /var/www/html/

# Changer le DocumentRoot si nécessaire
ENV APACHE_DOCUMENT_ROOT=/var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Permissions
RUN chown -R www-data:www-data /var/www/html

# Port
ENV PORT=8080
RUN sed -i "s/Listen 80/Listen \${PORT}/" /etc/apache2/ports.conf

EXPOSE 8080

CMD ["apache2-foreground"]
