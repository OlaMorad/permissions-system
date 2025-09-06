#!/bin/sh
set -e

echo "Waiting for MySQL to be ready..."

while ! mysqladmin ping -h "$DB_HOST" -P "$DB_PORT" --silent; do
    echo "MySQL is unavailable - sleeping"
    sleep 3
done

echo "MySQL is ready! Running migrations and clearing cache..."

# تعطيل foreign key مؤقتًا
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "SET FOREIGN_KEY_CHECKS=0;"

# تنفيذ المايغريشن
php artisan migrate --force

# تنظيف الكاش (config, route, view)
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# تفعيل foreign key بعد المايغريشن
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "SET FOREIGN_KEY_CHECKS=1;"

echo "Seeding database..."
php artisan db:seed --force

echo "Starting Laravel server..."
echo "PORT is $PORT"

# تشغيل السيرفر على البورت المحدد من Railway
exec php -S 0.0.0.0:$PORT -t public
