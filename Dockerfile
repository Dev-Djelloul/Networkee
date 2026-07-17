FROM php:8.2-apache

# Installer PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql pdo \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Désactiver les modules MPM conflictuels sauf prefork
RUN a2dismod mpm_event mpm_worker mpm_winnt 2>/dev/null || true && \
    a2enmod mpm_prefork

# Activer mod_rewrite
RUN a2enmod rewrite

# Copier le projet
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 8080

CMD ["apache2-foreground"]
