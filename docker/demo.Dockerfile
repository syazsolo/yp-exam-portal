FROM php:8.4-cli-alpine

WORKDIR /var/www/html

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

# Runtime libraries stay; build toolchain is installed virtual and removed
# after the PHP extensions are compiled to keep the image lean.
RUN apk add --no-cache \
        bash \
        curl \
        icu-libs \
        libzip \
        oniguruma \
        nodejs \
        npm \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-install bcmath intl mbstring pdo_mysql zip \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Build-time secrets/config supplied by docker-compose.demo.yml. They become
# baked into the package manifest (Composer post-autoload) and the Vite bundle.
# Runtime values come from compose `environment:` and override these.
ARG APP_KEY
ARG VITE_APP_NAME="YP Exam Portal"
ENV APP_KEY=$APP_KEY \
    VITE_APP_NAME=$VITE_APP_NAME

COPY composer.json composer.lock package.json package-lock.json ./
COPY artisan ./artisan
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY storage ./storage
COPY tests ./tests
COPY phpunit.xml ./phpunit.xml
COPY vite.config.js postcss.config.js tailwind.config.js jsconfig.json ./
COPY README.md docker-compose.demo.yml ./
COPY docker/mysql ./docker/mysql
COPY docker/demo.Dockerfile ./docker/demo.Dockerfile
COPY docker/demo-init.sh ./docker/demo-init.sh
COPY docker/demo-test.sh ./docker/demo-test.sh
RUN cp docker/demo-init.sh /usr/local/bin/demo-init.sh \
    && cp docker/demo-test.sh /usr/local/bin/demo-test.sh

RUN composer install --prefer-dist --optimize-autoloader \
    && npm ci \
    && npm run build \
    && npm cache clean --force \
    && rm -rf node_modules \
    && printf '# Configuration is supplied at runtime via the container environment.\n' > .env \
    && chmod +x /usr/local/bin/demo-init.sh /usr/local/bin/demo-test.sh \
    && chmod -R ug+rw storage bootstrap/cache public

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
