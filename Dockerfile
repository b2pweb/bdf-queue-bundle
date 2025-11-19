# the different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target


# https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact
ARG PHP_VERSION=8.2
ARG NGINX_VERSION=1.21


# "php" stage
FROM php:${PHP_VERSION}-fpm-alpine AS bdf

# persistent / runtime deps
RUN apk add --no-cache \
		acl \
		fcgi \
		file \
		gettext \
		git \
		freetype libpng libjpeg-turbo \
        bash \
        vim \
	;

ARG APCU_VERSION=5.1
RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
		$PHPIZE_DEPS \
		icu-dev \
		libzip-dev \
		zlib-dev \
		freetype-dev libpng-dev libjpeg-turbo-dev \
        gmp-dev \
        libxml2-dev \
	; \
	\
	docker-php-ext-configure zip; \
	docker-php-ext-configure gd \
		--with-freetype \
		--with-jpeg; \
	docker-php-ext-install -j$(nproc) \
		intl \
		pdo_mysql \
		zip \
		pcntl \
		gd \
		gmp \
		bcmath \
    soap \
#		sockets \
		mysqli \
	; \
	pecl install \
		apcu-${APCU_VERSION} \
		redis \
	; \
	pecl clear-cache; \
	docker-php-ext-enable \
#		apcu \
		opcache \
		pdo_mysql \
		redis \
	; \
	\
	runDeps="$( \
		scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
			| tr ',' '\n' \
			| sort -u \
			| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)"; \
	apk add --no-cache --virtual .api-phpexts-rundeps $runDeps; \
	\
	apk del .build-deps

RUN apk --no-cache update && apk --no-cache add bash
RUN wget https://get.symfony.com/cli/installer -O - | bash && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

WORKDIR /srv/bdf

CMD ["php-fpm"]
