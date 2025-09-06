#!/bin/sh
set -e

echo "Waiting for MySQL to be ready..."

while ! mysqladmin ping -h "$DB_HOST" -P "$DB_PORT" --silent; do
    echo "MySQL is unavailable - sleeping"
    sleep 3
done

echo "MySQL is ready! Running migrations..."
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "SET FOREIGN_KEY_CHECKS=0;"

php artisan db:wipe --force
php artisan migrate:fresh --force
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "SET FOREIGN_KEY_CHECKS=1;"

echo "Seeding database..."
php artisan db:seed --force

echo "Starting Laravel server..."
exec php artisan serve --host=0.0.0.0 --port=$PORT
echo "PORT is $PORT"
