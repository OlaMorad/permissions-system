# صورة الأساس
FROM laravelsail/php82-composer

# تثبيت امتداد MySQL والحزم المطلوبة
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libzip-dev \
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql mbstring zip

# مجلد العمل
WORKDIR /var/www/html

# نسخ ملفات المشروع
COPY . .

# تثبيت الاعتماديات
RUN composer install --optimize-autoloader

# نسخ السكربت وتشغيله عند بدء الحاوية
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# فتح البورت الافتراضي
EXPOSE 8000

# تشغيل entrypoint
CMD ["/usr/local/bin/entrypoint.sh"]
