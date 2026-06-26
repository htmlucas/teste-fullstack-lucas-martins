FROM php:8.4-fpm

# Atualiza pacotes do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev

# Instala extensões do PHP
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath

RUN pecl install redis && docker-php-ext-enable redis

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Cria usuário da aplicação
RUN groupadd -g 1000 laravel && \
    useradd -u 1000 -ms /bin/bash -g laravel laravel

# Copia o projeto
COPY . .

# Permissões
RUN chown -R laravel:laravel /var/www

# Usuário não-root
USER laravel

EXPOSE 9000

CMD ["php-fpm"]