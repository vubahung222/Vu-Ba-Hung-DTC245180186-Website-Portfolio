FROM php:8.2.18-fpm-alpine3.19

# Labels
LABEL maintainer="Portfolio Student"
LABEL description="Portfolio PHP-FPM Application"

# Cài extension cần thiết
RUN apk add --no-cache \
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
    && apk del libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev icu-dev

# PHP config
COPY php.ini /usr/local/etc/php/conf.d/portfolio.ini
COPY php-fpm.conf /usr/local/etc/php-fpm.d/portfolio.conf

# Tạo user non-root (www = uid 82 trên Alpine)
RUN addgroup -g 1000 -S appgroup && \
    adduser -u 1000 -S appuser -G appgroup -h /var/www/html && \
    chown -R appuser:appgroup /var/www/html

WORKDIR /var/www/html

# Copy source (mount override trong dev)
COPY --chown=appuser:appgroup . .

# Tạo thư mục upload với quyền ghi
RUN mkdir -p uploads/avatars && \
    chmod -R 755 uploads

USER appuser

EXPOSE 9000

CMD ["php-fpm"]
