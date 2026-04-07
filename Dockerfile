FROM php:7.3-apache

# Set timezone
ENV TZ=Asia/Karachi
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    zlib1g-dev \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) \
        mysqli \
        gd \
        mbstring \
        xml \
        zip \
        intl \
        opcache \
        pdo \
        pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite and suppress ServerName warning
RUN a2enmod rewrite \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Configure PHP settings to match XAMPP defaults
RUN { \
    echo 'upload_max_filesize = 64M'; \
    echo 'post_max_size = 64M'; \
    echo 'max_execution_time = 120000'; \
    echo 'max_input_time = 60'; \
    echo 'memory_limit = 256M'; \
    echo 'date.timezone = Asia/Karachi'; \
    echo 'error_reporting = E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT'; \
    echo 'display_errors = On'; \
    echo 'output_buffering = 4096'; \
} > /usr/local/etc/php/conf.d/custom.ini

# Set Apache document root
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy application files
COPY . /var/www/html/

# Fix missing assets that existed in the original XAMPP installation
# 1. bootstrap.min.css expected at /css/ - copy from erp/css/
RUN cp /var/www/html/erp/css/bootstrap.min.css /var/www/html/css/bootstrap.min.css
# 2. login-bg.mp4 - only login-bg2.mp4 exists; use it as the login video
RUN cp /var/www/html/video/login-bg2.mp4 /var/www/html/video/login-bg.mp4
# 3. javascript/ directory with login-anim-bg.js does not exist
RUN mkdir -p /var/www/html/javascript && \
    printf '(function(){\n  var v=document.getElementById("Video1");\n  var v2=document.getElementById("Video2");\n  if(v){v.play();} if(v2){v2.play();}\n})();\n' \
    > /var/www/html/javascript/login-anim-bg.js

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
