FROM php:8.1-apache

# Install system libraries needed for GD
# Install PHP extensions
# Enable Apache modules
# Set DirectoryIndex to index.php
# Set ServerName to suppress warning
RUN apt-get update && apt-get install -y \
    libjpeg-dev \
    libpng-dev \
    libgif-dev \
    libfreetype6-dev \
 && docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
 && docker-php-ext-install gd mysqli pdo pdo_mysql \
 && a2enmod rewrite dir \
 && sed -i 's/DirectoryIndex .*/DirectoryIndex index.php index.html/' /etc/apache2/mods-enabled/dir.conf \
 && echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
 && a2enconf servername \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*
 
EXPOSE 80
CMD ["apache2-foreground"]
