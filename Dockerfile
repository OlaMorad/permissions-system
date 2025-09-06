FROM laravelsail/php82-composer

RUN docker-php-ext-install pdo_mysql

WORKDIR /var/www/html

COPY . .

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql mbstring zip

RUN composer install --no-dev --optimize-autoloader
RUN cp .env.example .env || true
RUN php artisan key:generate
RUN php artisan jwt:secret

EXPOSE 8080

CMD php artisan serve --host=0.0.0.0 --port=${PORT}
