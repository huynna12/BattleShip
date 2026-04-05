FROM php:8.2-cli

RUN docker-php-ext-install mysqli

WORKDIR /app
# cache-bust: v2
COPY . .

CMD ["/bin/sh", "-c", "php -S 0.0.0.0:${PORT:-8080}"]
