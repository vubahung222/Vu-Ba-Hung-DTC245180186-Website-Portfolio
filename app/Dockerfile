FROM php:8.2.18-fpm-alpine3.19

# Labels
LABEL maintainer="Portfolio Student"
LABEL description="Portfolio PHP-FPM Application"

# Cài runtime libraries và build dependencies
RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    libwebp \
    freetype \
    icu-libs \
    icu-data-full \
    && apk add --no-cache --virtual .build-deps \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    icu-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        gd \
        intl \
        opcache \
        exif \
    && apk del .build-deps

# Dọn dẹp cấu hình mặc định của PHP-FPM để tránh xung đột cổng 9000
RUN rm -rf /usr/local/etc/php-fpm.d/*

# PHP config
COPY php.ini /usr/local/etc/php/conf.d/portfolio.ini
COPY php-fpm.conf /usr/local/etc/php-fpm.conf

# Tạo user non-root
RUN addgroup -g 1000 -S appgroup && \
    adduser -u 1000 -S appuser -G appgroup -h /var/www/html && \
    mkdir -p /usr/local/var/log && \
    chown -R appuser:appgroup /var/www/html /usr/local/var/log

WORKDIR /var/www/html

# Copy source (mount override trong dev)
COPY --chown=appuser:appgroup . .

# Tạo thư mục upload với quyền ghi
RUN mkdir -p uploads/avatars && \
    chmod -R 755 uploads

USER appuser

EXPOSE 9000

CMD ["php-fpm"]
