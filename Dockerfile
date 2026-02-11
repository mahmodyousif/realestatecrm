# استخدم نسخة PHP-FPM الرسمية
FROM php:8.2-fpm

# تثبيت المتطلبات الأساسية
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip unzip git curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mbstring bcmath exif pcntl

# تثبيت Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ضبط مجلد العمل
WORKDIR /var/www/html

# نسخ الملفات
COPY . .

# تثبيت الحزم
RUN composer install --optimize-autoloader --no-interaction --no-scripts

# توليد مفتاح Laravel
RUN php artisan key:generate

# تخزين الصلاحيات
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# تعريف البورت
EXPOSE 8000

# أمر التشغيل
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
