FROM laravelsail/php82-composer

# تثبيت امتداد pdo_mysql
RUN docker-php-ext-install pdo_mysql
