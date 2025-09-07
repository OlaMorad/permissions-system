# صورة الأساس
FROM laravelsail/php82-composer

# تثبيت امتداد MySQL
RUN docker-php-ext-install pdo_mysql

# مجلد العمل
WORKDIR /var/www/html

# نسخ ملفات المشروع
COPY . .

# تثبيت الحزم المطلوبة و gd و zip و mbstring
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libzip-dev \
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql mbstring zip


# تثبيت الاعتماديات
RUN composer install --optimize-autoloader

# إنشاء ملف .env من المثال إذا مش موجود
RUN cp .env.example .env || true

# توليد مفتاح التطبيق و JWT secret
RUN php artisan key:generate
RUN php artisan jwt:secret

# نسخ السكربت وتشغيله عند بدء الحاوية
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# فتح البورت الافتراضي
EXPOSE 8000

# تنظيف كاش Laravel
RUN php artisan config:clear \
    && php artisan cache:clear \
    && php artisan route:clear \
    && php artisan view:clear \
    && composer dump-autoload

# تشغيل entrypoint
CMD ["/usr/local/bin/entrypoint.sh"]
