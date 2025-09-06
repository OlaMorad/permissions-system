FROM php:8.2-fpm

# تثبيت المكتبات اللازمة
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libzip-dev unzip git curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mbstring zip exif pcntl bcmath

# تثبيت Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# نسخ المشروع
COPY . .

# تثبيت الاعتماديات
RUN composer install --optimize-autoloader --no-dev

# نسخ إعدادات nginx
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

# فتح بورت
EXPOSE 80

CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
