# The development image intentionally contains only PHP and Composer tooling.
# This package is a standalone library: it has no web server, database, or
# WordPress runtime.
# Deliberate digest pins make a local rebuild and CI use the same toolchain
# until this file is consciously updated.
ARG PHP_IMAGE=php:8.3.36-cli-bookworm@sha256:cdb77e306d6bb99db40e513fbe036ecc6c53b575cbcb7e581387845fef26d755
FROM composer:2@sha256:9715c7f69044da2a212a5fbde29ee7da24e364d426560ae6367b060236f847d7 AS composer

FROM ${PHP_IMAGE}

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN apt-get update \
    && apt-get install --yes --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

# Match the usual host developer UID to keep bind-mounted vendor/ writable.
RUN groupadd --gid 1000 app \
    && useradd --uid 1000 --gid app --create-home --shell /bin/bash app

USER app
WORKDIR /workspace

CMD ["composer", "ci"]
