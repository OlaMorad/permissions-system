FROM laravelsail/php82-composer

# تثبيت امتداد pdo_mysql
RUN docker-php-ext-install pdo_mysql

WORKDIR /var/www/html

COPY . .
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql mbstring zip

# تثبيت الاعتماديات
RUN composer install --no-dev --optimize-autoloader

# نسخ ملف env إذا مش موجود
RUN cp .env.example .env || true

# توليد مفتاح التطبيق
RUN php artisan key:generate
RUN php artisan jwt:secret

CMD sh -c "php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"


EXPOSE ${PORT}

# تشغيل السيرفر على البورت يلي Railway يعطيه
CMD php artisan serve --host=0.0.0.0 --port=${PORT}
