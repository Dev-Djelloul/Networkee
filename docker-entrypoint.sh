#!/bin/bash
set -e

# Définir le port (Railway utilise PORT, sinon 8080)
PORT=${PORT:-8080}

# Configurer Apache pour écouter sur le PORT
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf

# Démarrer Apache
exec apache2-foreground
