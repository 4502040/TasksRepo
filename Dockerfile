# Dockerfile
FROM php:8.2-fpm-alpine

WORKDIR /var/www

RUN apk update && apk add \
    bash \
    git \
    unzip \
    postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Установка Symfony CLI (опционально)
RUN wget https://get.symfony.com/cli/installer -O - | bash && \
    mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Копируем зависимости
##COPY composer.json ./
##RUN composer install --prefer-dist --no-scripts --no-progress --no-interaction

# Копируем весь проект
COPY . .

# Настройка прав
RUN chown -R www-data:www-data /var/www

EXPOSE 9000

CMD ["php-fpm"]