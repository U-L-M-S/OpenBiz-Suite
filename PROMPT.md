# 🚀 OpenBiz Suite - Complete Project Specification

> **Ein modulares, API-first Business Platform für KMU**
> 
> Dieses Dokument dient als vollständige Spezifikation für die Entwicklung der OpenBiz Suite.
> Alle Komponenten müssen mit `docker compose up --build` startbar sein.

---

## 📋 Inhaltsverzeichnis

1. [Projekt-Übersicht](#1-projekt-übersicht)
2. [Technologie-Stack](#2-technologie-stack)
3. [Container-Architektur](#3-container-architektur)
4. [Modul-Spezifikationen](#4-modul-spezifikationen)
5. [Datenbank-Design](#5-datenbank-design)
6. [API-Spezifikation](#6-api-spezifikation)
7. [Frontend-Design](#7-frontend-design)
8. [Workflow Engine](#8-workflow-engine)
9. [AI Integration](#9-ai-integration)
10. [Sicherheit](#10-sicherheit)
11. [Verzeichnisstruktur](#11-verzeichnisstruktur)
12. [Deployment](#12-deployment)

---

## 1. Projekt-Übersicht

### 1.1 Vision

OpenBiz Suite ist eine **modulare, selbst-hostbare Business-Plattform**, die folgende Lücken im Markt schließt:

| Konkurrent | Was fehlt | OpenBiz löst |
|------------|-----------|--------------|
| HRworks | Keine öffentliche API, kein LMS | ✅ Full REST/GraphQL API + LMS |
| Lexware | Keine HR-Integration | ✅ HR + Buchhaltungs-Bridge |
| Haufe | Kein Self-Hosting, teuer | ✅ Open Source, Docker-ready |
| Alle | Keine Workflow-Automation | ✅ n8n-style Workflow Engine |

### 1.2 Kernprinzipien

```
┌─────────────────────────────────────────────────────────────────┐
│                     DESIGN PRINCIPLES                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  🔌 API-FIRST        Jede Funktion über API erreichbar          │
│  📦 MODULAR          Module unabhängig aktivierbar              │
│  🏢 MULTI-TENANT     Eine Installation, viele Firmen            │
│  🐳 CONTAINER-READY  Ein Befehl zum Starten                     │
│  🔒 SECURE           OAuth 2.0, RBAC, Audit Logs                │
│  🌐 OFFLINE-CAPABLE  PWA mit Service Workers                    │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 1.3 Zielgruppen

- **Kleine/Mittlere Unternehmen** (10-500 Mitarbeiter)
- **IT-Dienstleister** die Kunden betreuen
- **Startups** die schnell skalieren wollen
- **Entwickler** die eine erweiterbare Plattform suchen

---

## 2. Technologie-Stack

### 2.1 Backend

```
┌─────────────────────────────────────────────────────────────────┐
│                      BACKEND STACK                               │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Framework:        Laravel 11.x (PHP 8.3)                       │
│  API:              Laravel Sanctum + Lighthouse (GraphQL)       │
│  Queue:            Laravel Horizon (Redis)                       │
│  Search:           Laravel Scout + Meilisearch                  │
│  File Storage:     Laravel + MinIO (S3-compatible)              │
│  Cache:            Redis                                         │
│  Database:         MySQL 8.0                                     │
│  PDF Generation:   DomPDF / Browsershot                         │
│  Excel:            Laravel Excel (Maatwebsite)                  │
│  Testing:          Pest PHP                                      │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 2.2 Frontend

```
┌─────────────────────────────────────────────────────────────────┐
│                      FRONTEND STACK                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  UI Framework:     Livewire 3 + Alpine.js (TALL Stack)          │
│  CSS:              Tailwind CSS 3.x                             │
│  Components:       Flux UI / Filament Components                │
│  Charts:           Chart.js + Livewire Charts                   │
│  Icons:            Heroicons + Lucide                           │
│  Notifications:    Toaster (Livewire)                           │
│  Modals:           Wire Elements Modal                          │
│  Tables:           Livewire Tables (Rappasoft)                  │
│  Forms:            Filament Forms                               │
│  Admin Panel:      Filament 3.x                                 │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 2.3 DevOps

```
┌─────────────────────────────────────────────────────────────────┐
│                      DEVOPS STACK                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Containerization: Docker + Docker Compose                      │
│  Web Server:       Nginx (Alpine)                               │
│  PHP Runtime:      PHP-FPM 8.3 (Alpine)                         │
│  CI/CD:            GitHub Actions                               │
│  Monitoring:       Laravel Telescope (Dev) / Pulse (Prod)       │
│  Logging:          Monolog → JSON → Stdout                      │
│  SSL:              Let's Encrypt (Traefik) / Self-signed (Dev)  │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 3. Container-Architektur

### 3.1 Container-Übersicht

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           DOCKER CONTAINER ARCHITECTURE                          │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│                              ┌─────────────────┐                                │
│                              │   TRAEFIK       │                                │
│                              │   (Reverse      │                                │
│                              │    Proxy)       │                                │
│                              │   :80 :443      │                                │
│                              └────────┬────────┘                                │
│                                       │                                          │
│         ┌─────────────────────────────┼─────────────────────────────┐           │
│         │                             │                             │           │
│         ▼                             ▼                             ▼           │
│  ┌─────────────┐              ┌─────────────┐              ┌─────────────┐      │
│  │   NGINX     │              │   NGINX     │              │  MEILISEARCH│      │
│  │   (Web)     │              │   (API)     │              │  (Search)   │      │
│  │   :8080     │              │   :8081     │              │   :7700     │      │
│  └──────┬──────┘              └──────┬──────┘              └─────────────┘      │
│         │                            │                                          │
│         ▼                            ▼                                          │
│  ┌─────────────┐              ┌─────────────┐                                   │
│  │  PHP-FPM    │◄────────────►│  PHP-FPM    │                                   │
│  │  (App)      │   Shared     │  (Worker)   │                                   │
│  │             │   Volume     │  Horizon    │                                   │
│  └──────┬──────┘              └──────┬──────┘                                   │
│         │                            │                                          │
│         └─────────────┬──────────────┘                                          │
│                       │                                                          │
│         ┌─────────────┼─────────────┬─────────────┬─────────────┐               │
│         ▼             ▼             ▼             ▼             ▼               │
│  ┌─────────────┐┌─────────────┐┌─────────────┐┌─────────────┐┌─────────────┐    │
│  │   MySQL     ││   Redis     ││   MinIO     ││  Mailhog    ││  Soketi     │    │
│  │   :3306     ││   :6379     ││   :9000     ││  :8025      ││  :6001      │    │
│  │   Primary   ││   Cache +   ││   S3-compat ││  Dev Mail   ││  WebSocket  │    │
│  │   Database  ││   Queue     ││   Storage   ││  Testing    ││  Server     │    │
│  └─────────────┘└─────────────┘└─────────────┘└─────────────┘└─────────────┘    │
│                                                                                  │
│  ═══════════════════════════════════════════════════════════════════════════    │
│                              DOCKER NETWORKS                                     │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │  openbiz_frontend   │  openbiz_backend   │  openbiz_storage             │    │
│  │  (Traefik, Nginx)   │  (PHP, MySQL, etc) │  (MinIO, Backups)            │    │
│  └─────────────────────────────────────────────────────────────────────────┘    │
│                                                                                  │
│  ═══════════════════════════════════════════════════════════════════════════    │
│                              DOCKER VOLUMES                                      │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │  mysql_data  │  redis_data  │  minio_data  │  meilisearch_data          │    │
│  └─────────────────────────────────────────────────────────────────────────┘    │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 3.2 Container-Abhängigkeiten

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                         CONTAINER DEPENDENCY GRAPH                               │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│                              ┌─────────┐                                        │
│                              │ traefik │                                        │
│                              └────┬────┘                                        │
│                                   │                                              │
│                    ┌──────────────┼──────────────┐                              │
│                    ▼              ▼              ▼                              │
│               ┌─────────┐   ┌─────────┐   ┌─────────────┐                       │
│               │  nginx  │   │nginx-api│   │ meilisearch │                       │
│               └────┬────┘   └────┬────┘   └─────────────┘                       │
│                    │             │                                               │
│                    ▼             ▼                                               │
│               ┌─────────────────────────┐                                       │
│               │         app             │                                       │
│               │      (PHP-FPM)          │                                       │
│               └────────────┬────────────┘                                       │
│                            │                                                     │
│         ┌──────────────────┼──────────────────┬──────────────┐                  │
│         ▼                  ▼                  ▼              ▼                  │
│    ┌─────────┐        ┌─────────┐        ┌─────────┐   ┌─────────┐             │
│    │  mysql  │        │  redis  │        │  minio  │   │ soketi  │             │
│    └─────────┘        └────┬────┘        └─────────┘   └─────────┘             │
│                            │                                                     │
│                            ▼                                                     │
│                       ┌─────────┐                                               │
│                       │ worker  │ (Horizon - depends on redis)                  │
│                       └─────────┘                                               │
│                                                                                  │
│  LEGENDE:                                                                        │
│  ────────                                                                        │
│  ─────► = depends_on                                                            │
│  ┌───┐  = Container                                                             │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 3.3 Docker Compose Spezifikation

```yaml
# docker-compose.yml
version: '3.8'

services:
  # ============================================
  # REVERSE PROXY
  # ============================================
  traefik:
    image: traefik:v3.0
    container_name: openbiz_traefik
    command:
      - "--api.insecure=true"
      - "--providers.docker=true"
      - "--providers.docker.exposedbydefault=false"
      - "--entrypoints.web.address=:80"
      - "--entrypoints.websecure.address=:443"
    ports:
      - "80:80"
      - "443:443"
      - "8080:8080"  # Traefik Dashboard
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock:ro
      - ./docker/traefik/certs:/certs
    networks:
      - openbiz_frontend
    restart: unless-stopped

  # ============================================
  # WEB SERVER (Frontend)
  # ============================================
  nginx:
    image: nginx:1.25-alpine
    container_name: openbiz_nginx
    volumes:
      - ./src:/var/www/html:ro
      - ./docker/nginx/conf.d/app.conf:/etc/nginx/conf.d/default.conf:ro
    depends_on:
      - app
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.openbiz.rule=Host(`openbiz.local`)"
      - "traefik.http.services.openbiz.loadbalancer.server.port=80"
    networks:
      - openbiz_frontend
      - openbiz_backend
    restart: unless-stopped

  # ============================================
  # PHP APPLICATION
  # ============================================
  app:
    build:
      context: ./docker/php
      dockerfile: Dockerfile
      args:
        PHP_VERSION: 8.3
        NODE_VERSION: 20
    container_name: openbiz_app
    working_dir: /var/www/html
    volumes:
      - ./src:/var/www/html
      - ./docker/php/php.ini:/usr/local/etc/php/conf.d/custom.ini:ro
    environment:
      APP_ENV: ${APP_ENV:-local}
      APP_DEBUG: ${APP_DEBUG:-true}
      DB_HOST: mysql
      DB_DATABASE: ${DB_DATABASE:-openbiz}
      DB_USERNAME: ${DB_USERNAME:-openbiz}
      DB_PASSWORD: ${DB_PASSWORD:-secret}
      REDIS_HOST: redis
      CACHE_DRIVER: redis
      QUEUE_CONNECTION: redis
      SESSION_DRIVER: redis
      FILESYSTEM_DISK: minio
      AWS_ENDPOINT: http://minio:9000
      AWS_ACCESS_KEY_ID: ${MINIO_ACCESS_KEY:-openbiz}
      AWS_SECRET_ACCESS_KEY: ${MINIO_SECRET_KEY:-secret123}
      AWS_BUCKET: ${MINIO_BUCKET:-openbiz}
      AWS_USE_PATH_STYLE_ENDPOINT: "true"
      MEILISEARCH_HOST: http://meilisearch:7700
      BROADCAST_DRIVER: pusher
      PUSHER_HOST: soketi
      PUSHER_PORT: 6001
      PUSHER_SCHEME: http
    depends_on:
      mysql:
        condition: service_healthy
      redis:
        condition: service_healthy
      minio:
        condition: service_started
    networks:
      - openbiz_backend
    restart: unless-stopped

  # ============================================
  # QUEUE WORKER (Horizon)
  # ============================================
  worker:
    build:
      context: ./docker/php
      dockerfile: Dockerfile
    container_name: openbiz_worker
    working_dir: /var/www/html
    command: php artisan horizon
    volumes:
      - ./src:/var/www/html
    environment:
      APP_ENV: ${APP_ENV:-local}
      DB_HOST: mysql
      DB_DATABASE: ${DB_DATABASE:-openbiz}
      DB_USERNAME: ${DB_USERNAME:-openbiz}
      DB_PASSWORD: ${DB_PASSWORD:-secret}
      REDIS_HOST: redis
    depends_on:
      - app
      - redis
    networks:
      - openbiz_backend
    restart: unless-stopped

  # ============================================
  # SCHEDULER (Cron)
  # ============================================
  scheduler:
    build:
      context: ./docker/php
      dockerfile: Dockerfile
    container_name: openbiz_scheduler
    working_dir: /var/www/html
    command: >
      sh -c "while true; do
        php artisan schedule:run --verbose --no-interaction &
        sleep 60
      done"
    volumes:
      - ./src:/var/www/html
    depends_on:
      - app
    networks:
      - openbiz_backend
    restart: unless-stopped

  # ============================================
  # DATABASE
  # ============================================
  mysql:
    image: mysql:8.0
    container_name: openbiz_mysql
    command: --default-authentication-plugin=mysql_native_password
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:-rootsecret}
      MYSQL_DATABASE: ${DB_DATABASE:-openbiz}
      MYSQL_USER: ${DB_USERNAME:-openbiz}
      MYSQL_PASSWORD: ${DB_PASSWORD:-secret}
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/init:/docker-entrypoint-initdb.d:ro
    ports:
      - "3306:3306"
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 5s
      retries: 5
    networks:
      - openbiz_backend
    restart: unless-stopped

  # ============================================
  # CACHE & QUEUE
  # ============================================
  redis:
    image: redis:7-alpine
    container_name: openbiz_redis
    command: redis-server --appendonly yes --maxmemory 256mb --maxmemory-policy allkeys-lru
    volumes:
      - redis_data:/data
    ports:
      - "6379:6379"
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5
    networks:
      - openbiz_backend
    restart: unless-stopped

  # ============================================
  # OBJECT STORAGE (S3-compatible)
  # ============================================
  minio:
    image: minio/minio:latest
    container_name: openbiz_minio
    command: server /data --console-address ":9001"
    environment:
      MINIO_ROOT_USER: ${MINIO_ACCESS_KEY:-openbiz}
      MINIO_ROOT_PASSWORD: ${MINIO_SECRET_KEY:-secret123}
    volumes:
      - minio_data:/data
    ports:
      - "9000:9000"
      - "9001:9001"  # MinIO Console
    networks:
      - openbiz_backend
      - openbiz_storage
    restart: unless-stopped

  # ============================================
  # SEARCH ENGINE
  # ============================================
  meilisearch:
    image: getmeili/meilisearch:v1.6
    container_name: openbiz_meilisearch
    environment:
      MEILI_MASTER_KEY: ${MEILISEARCH_KEY:-masterkey123}
      MEILI_ENV: development
    volumes:
      - meilisearch_data:/meili_data
    ports:
      - "7700:7700"
    networks:
      - openbiz_backend
    restart: unless-stopped

  # ============================================
  # WEBSOCKET SERVER
  # ============================================
  soketi:
    image: quay.io/soketi/soketi:1.6-16-debian
    container_name: openbiz_soketi
    environment:
      SOKETI_DEBUG: "1"
      SOKETI_DEFAULT_APP_ID: openbiz
      SOKETI_DEFAULT_APP_KEY: openbiz-key
      SOKETI_DEFAULT_APP_SECRET: openbiz-secret
    ports:
      - "6001:6001"
      - "9601:9601"  # Metrics
    networks:
      - openbiz_backend
    restart: unless-stopped

  # ============================================
  # MAIL (Development)
  # ============================================
  mailhog:
    image: mailhog/mailhog:latest
    container_name: openbiz_mailhog
    ports:
      - "1025:1025"   # SMTP
      - "8025:8025"   # Web UI
    networks:
      - openbiz_backend
    profiles:
      - dev

  # ============================================
  # DATABASE ADMIN (Development)
  # ============================================
  phpmyadmin:
    image: phpmyadmin:latest
    container_name: openbiz_phpmyadmin
    environment:
      PMA_HOST: mysql
      PMA_USER: ${DB_USERNAME:-openbiz}
      PMA_PASSWORD: ${DB_PASSWORD:-secret}
    ports:
      - "8081:80"
    depends_on:
      - mysql
    networks:
      - openbiz_backend
    profiles:
      - dev

# ============================================
# NETWORKS
# ============================================
networks:
  openbiz_frontend:
    driver: bridge
  openbiz_backend:
    driver: bridge
  openbiz_storage:
    driver: bridge

# ============================================
# VOLUMES
# ============================================
volumes:
  mysql_data:
    driver: local
  redis_data:
    driver: local
  minio_data:
    driver: local
  meilisearch_data:
    driver: local
```

### 3.4 PHP Dockerfile

```dockerfile
# docker/php/Dockerfile
ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-fpm-alpine

# Arguments
ARG NODE_VERSION=20

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    icu-dev \
    oniguruma-dev \
    libxml2-dev \
    nodejs \
    npm \
    supervisor

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
        xml

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create user
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www

# Set working directory
WORKDIR /var/www/html

# Copy custom PHP config
COPY php.ini /usr/local/etc/php/conf.d/custom.ini
COPY www.conf /usr/local/etc/php-fpm.d/www.conf

# Set permissions
RUN chown -R www:www /var/www/html

USER www

EXPOSE 9000

CMD ["php-fpm"]
```

---

## 4. Modul-Spezifikationen

### 4.1 Modul-Architektur

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           MODULE ARCHITECTURE                                    │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│                              ┌─────────────────┐                                │
│                              │   CORE MODULE   │                                │
│                              │                 │                                │
│                              │ • Multi-Tenant  │                                │
│                              │ • Auth/RBAC     │                                │
│                              │ • Settings      │                                │
│                              │ • Audit Log     │                                │
│                              │ • Notifications │                                │
│                              │ • File Manager  │                                │
│                              └────────┬────────┘                                │
│                                       │                                          │
│                    ┌──────────────────┼──────────────────┐                      │
│                    │                  │                  │                      │
│         ┌──────────▼────────┐ ┌──────▼───────┐ ┌───────▼────────┐              │
│         │    HR MODULE      │ │ ASSET MODULE │ │   LMS MODULE   │              │
│         │                   │ │              │ │                │              │
│         │ • Employees       │ │ • Assets     │ │ • Courses      │              │
│         │ • Departments     │ │ • Categories │ │ • Lessons      │              │
│         │ • Positions       │ │ • Locations  │ │ • Quizzes      │              │
│         │ • Time Tracking   │ │ • QR/Barcode │ │ • Certificates │              │
│         │ • Leave Mgmt      │ │ • Transfers  │ │ • Enrollments  │              │
│         │ • Onboarding      │ │ • Maintenance│ │ • Progress     │              │
│         │ • Documents       │ │ • Audit Trail│ │ • Gamification │              │
│         └──────────┬────────┘ └──────┬───────┘ └───────┬────────┘              │
│                    │                 │                 │                        │
│                    └─────────────────┼─────────────────┘                        │
│                                      │                                          │
│         ┌──────────────────┐ ┌───────▼───────┐ ┌──────────────────┐            │
│         │  SHOP MODULE     │ │   WORKFLOW    │ │   API GATEWAY    │            │
│         │                  │ │    ENGINE     │ │                  │            │
│         │ • Products       │ │               │ │ • REST API       │            │
│         │ • Categories     │ │ • Triggers    │ │ • GraphQL        │            │
│         │ • Orders         │ │ • Conditions  │ │ • Webhooks       │            │
│         │ • Payments       │ │ • Actions     │ │ • Rate Limiting  │            │
│         │ • Shipping       │ │ • Templates   │ │ • API Keys       │            │
│         │ • Invoices       │ │ • Logs        │ │ • Documentation  │            │
│         └──────────────────┘ └───────────────┘ └──────────────────┘            │
│                                                                                  │
│  ═══════════════════════════════════════════════════════════════════════════    │
│                           CROSS-CUTTING CONCERNS                                 │
│  ┌────────────────────────────────────────────────────────────────────────┐     │
│  │  Events │ Queues │ Caching │ Search │ Storage │ Logging │ AI Service  │     │
│  └────────────────────────────────────────────────────────────────────────┘     │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 4.2 Core Module

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              CORE MODULE                                         │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  FEATURES:                                                                       │
│  ─────────                                                                       │
│                                                                                  │
│  1. MULTI-TENANCY                                                               │
│     ┌─────────────────────────────────────────────────────────────────────┐     │
│     │  Strategy: Single Database with tenant_id foreign key               │     │
│     │                                                                     │     │
│     │  ┌─────────┐     ┌─────────┐     ┌─────────┐                       │     │
│     │  │ Tenant A│     │ Tenant B│     │ Tenant C│                       │     │
│     │  │ tenant_1│     │ tenant_2│     │ tenant_3│                       │     │
│     │  └────┬────┘     └────┬────┘     └────┬────┘                       │     │
│     │       │               │               │                             │     │
│     │       └───────────────┼───────────────┘                             │     │
│     │                       ▼                                             │     │
│     │              ┌─────────────────┐                                    │     │
│     │              │   employees     │                                    │     │
│     │              │ ─────────────── │                                    │     │
│     │              │ id              │                                    │     │
│     │              │ tenant_id  ◄────│──── Automatic scope               │     │
│     │              │ name            │                                    │     │
│     │              │ email           │                                    │     │
│     │              └─────────────────┘                                    │     │
│     └─────────────────────────────────────────────────────────────────────┘     │
│                                                                                  │
│  2. AUTHENTICATION & AUTHORIZATION                                              │
│     ┌─────────────────────────────────────────────────────────────────────┐     │
│     │                                                                     │     │
│     │  Auth Methods:                                                      │     │
│     │  • Email/Password (bcrypt)                                          │     │
│     │  • OAuth 2.0 (Google, Microsoft, GitHub)                           │     │
│     │  • SAML 2.0 (Enterprise SSO)                                       │     │
│     │  • API Tokens (Sanctum)                                            │     │
│     │  • 2FA (TOTP - Google Authenticator)                               │     │
│     │                                                                     │     │
│     │  RBAC Structure:                                                    │     │
│     │  ┌────────────────────────────────────────────────────────┐        │     │
│     │  │                                                        │        │     │
│     │  │   User ───► Roles ───► Permissions                    │        │     │
│     │  │    │                       │                           │        │     │
│     │  │    │    ┌─────────────────┼─────────────────┐         │        │     │
│     │  │    │    │                 │                 │         │        │     │
│     │  │    │    ▼                 ▼                 ▼         │        │     │
│     │  │    │  admin          hr.manager       asset.viewer    │        │     │
│     │  │    │    │                 │                 │         │        │     │
│     │  │    │    ▼                 ▼                 ▼         │        │     │
│     │  │    │ users.*         employees.*      assets.view     │        │     │
│     │  │    │ settings.*      leave.*          assets.scan     │        │     │
│     │  │    │ modules.*       timesheets.*                     │        │     │
│     │  │    │                                                  │        │     │
│     │  │    └──────────────────────────────────────────────────┘        │     │
│     │  │                                                        │        │     │
│     │  └────────────────────────────────────────────────────────┘        │     │
│     └─────────────────────────────────────────────────────────────────────┘     │
│                                                                                  │
│  3. AUDIT LOGGING                                                               │
│     ┌─────────────────────────────────────────────────────────────────────┐     │
│     │                                                                     │     │
│     │  Every model change is logged:                                      │     │
│     │                                                                     │     │
│     │  {                                                                  │     │
│     │    "id": "uuid",                                                    │     │
│     │    "tenant_id": 1,                                                  │     │
│     │    "user_id": 42,                                                   │     │
│     │    "event": "updated",                                              │     │
│     │    "auditable_type": "App\\Models\\Employee",                       │     │
│     │    "auditable_id": 123,                                             │     │
│     │    "old_values": {"salary": 50000},                                 │     │
│     │    "new_values": {"salary": 55000},                                 │     │
│     │    "ip_address": "192.168.1.100",                                   │     │
│     │    "user_agent": "Mozilla/5.0...",                                  │     │
│     │    "created_at": "2024-01-15T10:30:00Z"                            │     │
│     │  }                                                                  │     │
│     │                                                                     │     │
│     └─────────────────────────────────────────────────────────────────────┘     │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 4.3 HR Module

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                               HR MODULE                                          │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  ENTITIES:                                                                       │
│  ─────────                                                                       │
│                                                                                  │
│  ┌───────────────┐     ┌───────────────┐     ┌───────────────┐                  │
│  │  Department   │     │   Position    │     │   Employee    │                  │
│  ├───────────────┤     ├───────────────┤     ├───────────────┤                  │
│  │ id            │     │ id            │     │ id            │                  │
│  │ tenant_id     │     │ tenant_id     │     │ tenant_id     │                  │
│  │ name          │     │ department_id │◄────│ position_id   │                  │
│  │ parent_id     │◄────│ title         │     │ user_id       │                  │
│  │ manager_id    │     │ min_salary    │     │ employee_no   │                  │
│  │ cost_center   │     │ max_salary    │     │ first_name    │                  │
│  └───────────────┘     │ is_remote     │     │ last_name     │                  │
│                        └───────────────┘     │ email         │                  │
│                                              │ phone         │                  │
│                                              │ hire_date     │                  │
│                                              │ salary        │                  │
│                                              │ status        │                  │
│                                              │ manager_id    │──┐               │
│                                              └───────────────┘  │               │
│                                                     ▲           │               │
│                                                     └───────────┘               │
│                                                     (self-reference)            │
│                                                                                  │
│  ┌───────────────┐     ┌───────────────┐     ┌───────────────┐                  │
│  │  TimeEntry    │     │ LeaveRequest  │     │   Document    │                  │
│  ├───────────────┤     ├───────────────┤     ├───────────────┤                  │
│  │ id            │     │ id            │     │ id            │                  │
│  │ employee_id   │     │ employee_id   │     │ employee_id   │                  │
│  │ date          │     │ leave_type_id │     │ type          │                  │
│  │ clock_in      │     │ start_date    │     │ title         │                  │
│  │ clock_out     │     │ end_date      │     │ file_path     │                  │
│  │ break_minutes │     │ days          │     │ expires_at    │                  │
│  │ location      │     │ reason        │     │ is_verified   │                  │
│  │ notes         │     │ status        │     │ verified_by   │                  │
│  │ is_remote     │     │ approved_by   │     └───────────────┘                  │
│  └───────────────┘     │ approved_at   │                                        │
│                        └───────────────┘                                        │
│                                                                                  │
│  UI COMPONENTS:                                                                  │
│  ──────────────                                                                  │
│                                                                                  │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │                         HR DASHBOARD                                     │    │
│  ├─────────────────────────────────────────────────────────────────────────┤    │
│  │  ┌────────────────┐ ┌────────────────┐ ┌────────────────┐ ┌───────────┐ │    │
│  │  │  👥 Employees  │ │  ⏱️ Hours Today │ │  🏖️ On Leave  │ │ 📝 Pending│ │    │
│  │  │     127        │ │     847.5      │ │      12       │ │     8     │ │    │
│  │  │   +3 this mo.  │ │   Avg: 6.7h    │ │   3 remote    │ │  requests │ │    │
│  │  └────────────────┘ └────────────────┘ └────────────────┘ └───────────┘ │    │
│  │                                                                         │    │
│  │  ┌─────────────────────────────────────┐ ┌───────────────────────────┐ │    │
│  │  │       ATTENDANCE TODAY              │ │    UPCOMING EVENTS        │ │    │
│  │  │  ┌────┬────────┬────────┬────────┐  │ │                           │ │    │
│  │  │  │Time│ Name   │ Status │Location│  │ │  📅 15.01 Anna Birthday   │ │    │
│  │  │  ├────┼────────┼────────┼────────┤  │ │  📅 20.01 Max Probation   │ │    │
│  │  │  │9:01│ Max M. │ ✅ In  │ Office │  │ │  📅 25.01 Team Meeting    │ │    │
│  │  │  │9:15│ Anna S.│ ✅ In  │ Remote │  │ │  📅 31.01 Payroll Due     │ │    │
│  │  │  │9:30│ Tom B. │ 🏖️ PTO │   -    │  │ │                           │ │    │
│  │  │  └────┴────────┴────────┴────────┘  │ └───────────────────────────┘ │    │
│  │  └─────────────────────────────────────┘                               │    │
│  │                                                                         │    │
│  │  ┌───────────────────────────────────────────────────────────────────┐ │    │
│  │  │                    DEPARTMENT OVERVIEW                            │ │    │
│  │  │                                                                   │ │    │
│  │  │   Engineering ████████████████████ 45                            │ │    │
│  │  │   Sales       ████████████ 28                                    │ │    │
│  │  │   Marketing   ████████ 20                                        │ │    │
│  │  │   HR          ████ 12                                            │ │    │
│  │  │   Finance     ████ 10                                            │ │    │
│  │  │   Support     ████ 12                                            │ │    │
│  │  │                                                                   │ │    │
│  │  └───────────────────────────────────────────────────────────────────┘ │    │
│  └─────────────────────────────────────────────────────────────────────────┘    │
│                                                                                  │
│  WORKFLOWS:                                                                      │
│  ──────────                                                                      │
│                                                                                  │
│  1. Employee Onboarding                                                          │
│     ┌────────┐   ┌─────────┐   ┌─────────┐   ┌─────────┐   ┌────────┐          │
│     │ Create │──►│ Assign  │──►│ IT      │──►│Required │──►│Welcome │          │
│     │Employee│   │ Manager │   │ Assets  │   │Trainings│   │ Email  │          │
│     └────────┘   └─────────┘   └─────────┘   └─────────┘   └────────┘          │
│                                                                                  │
│  2. Leave Request                                                                │
│     ┌────────┐   ┌─────────┐   ┌─────────┐   ┌─────────┐                        │
│     │Request │──►│ Manager │──►│ HR      │──►│Calendar │                        │
│     │ Leave  │   │ Approval│   │ Approval│   │ Update  │                        │
│     └────────┘   └─────────┘   └─────────┘   └─────────┘                        │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 4.4 Asset Module

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              ASSET MODULE                                        │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  ENTITIES:                                                                       │
│  ─────────                                                                       │
│                                                                                  │
│  ┌───────────────┐     ┌───────────────┐     ┌───────────────┐                  │
│  │AssetCategory  │     │   Location    │     │    Asset      │                  │
│  ├───────────────┤     ├───────────────┤     ├───────────────┤                  │
│  │ id            │     │ id            │     │ id            │                  │
│  │ tenant_id     │     │ tenant_id     │     │ tenant_id     │                  │
│  │ name          │◄────│ name          │◄────│ category_id   │                  │
│  │ parent_id     │     │ building      │     │ location_id   │                  │
│  │ icon          │     │ floor         │     │ assigned_to   │──► Employee     │
│  │ depreciation  │     │ room          │     │ asset_tag     │                  │
│  │ useful_life   │     │ address       │     │ serial_number │                  │
│  └───────────────┘     │ qr_code       │     │ name          │                  │
│                        └───────────────┘     │ description   │                  │
│                                              │ purchase_date │                  │
│                                              │ purchase_cost │                  │
│                                              │ warranty_end  │                  │
│                                              │ status        │                  │
│                                              │ qr_code       │                  │
│                                              │ barcode       │                  │
│                                              └───────────────┘                  │
│                                                     │                           │
│                                                     ▼                           │
│                        ┌───────────────┐     ┌───────────────┐                  │
│                        │AssetTransfer  │     │ Maintenance   │                  │
│                        ├───────────────┤     ├───────────────┤                  │
│                        │ id            │     │ id            │                  │
│                        │ asset_id      │     │ asset_id      │                  │
│                        │ from_location │     │ type          │                  │
│                        │ to_location   │     │ scheduled_at  │                  │
│                        │ from_employee │     │ completed_at  │                  │
│                        │ to_employee   │     │ cost          │                  │
│                        │ transferred_by│     │ notes         │                  │
│                        │ notes         │     │ performed_by  │                  │
│                        │ transferred_at│     │ next_due      │                  │
│                        └───────────────┘     └───────────────┘                  │
│                                                                                  │
│  QR CODE SYSTEM:                                                                 │
│  ───────────────                                                                 │
│                                                                                  │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │                         QR CODE WORKFLOW                                 │    │
│  │                                                                         │    │
│  │  1. Generate             2. Print               3. Scan                 │    │
│  │  ┌─────────────┐         ┌─────────────┐        ┌─────────────┐         │    │
│  │  │ ▓▓▓▓▓▓▓▓▓▓▓ │         │ Asset Label │        │ 📱 Mobile   │         │    │
│  │  │ ▓▓▓ QR ▓▓▓▓ │  ────►  │ ┌─────────┐ │ ────►  │    App      │         │    │
│  │  │ ▓▓▓▓▓▓▓▓▓▓▓ │         │ │ QR Code │ │        │             │         │    │
│  │  │             │         │ └─────────┘ │        │  [Scan QR]  │         │    │
│  │  │ AST-001234  │         │ AST-001234  │        │             │         │    │
│  │  └─────────────┘         │ Dell XPS 15 │        └──────┬──────┘         │    │
│  │                          │ IT Dept.    │               │                │    │
│  │                          └─────────────┘               ▼                │    │
│  │                                                 ┌─────────────┐         │    │
│  │                                                 │ Asset Detail│         │    │
│  │                                                 │ Quick Action│         │    │
│  │                                                 │ • Transfer  │         │    │
│  │                                                 │ • Report    │         │    │
│  │                                                 │ • History   │         │    │
│  │                                                 └─────────────┘         │    │
│  └─────────────────────────────────────────────────────────────────────────┘    │
│                                                                                  │
│  QR Code Content Format:                                                         │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │  https://app.openbiz.local/assets/scan/{uuid}                           │    │
│  │                                                                         │    │
│  │  UUID ensures:                                                          │    │
│  │  • No sequential guessing                                               │    │
│  │  • Works across tenants (tenant resolved from asset)                   │    │
│  │  • Can be regenerated if compromised                                   │    │
│  └─────────────────────────────────────────────────────────────────────────┘    │
│                                                                                  │
│  LABEL TEMPLATES:                                                                │
│  ────────────────                                                                │
│                                                                                  │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │                                                                         │    │
│  │  Template: Standard (50x25mm)      Template: Large (100x50mm)          │    │
│  │  ┌─────────────────────────┐       ┌─────────────────────────────────┐ │    │
│  │  │ ┌─────┐                 │       │ ┌─────────┐                     │ │    │
│  │  │ │ QR  │ Dell XPS 15     │       │ │   QR    │  Dell XPS 15        │ │    │
│  │  │ │     │ AST-001234      │       │ │  Code   │  Serial: ABC123     │ │    │
│  │  │ └─────┘                 │       │ │         │  AST-001234         │ │    │
│  │  └─────────────────────────┘       │ └─────────┘  IT Department      │ │    │
│  │                                    │              Purchased: 2024     │ │    │
│  │                                    └─────────────────────────────────┘ │    │
│  │                                                                         │    │
│  └─────────────────────────────────────────────────────────────────────────┘    │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 4.5 LMS Module

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                               LMS MODULE                                         │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  ENTITIES:                                                                       │
│  ─────────                                                                       │
│                                                                                  │
│  ┌───────────────┐     ┌───────────────┐     ┌───────────────┐                  │
│  │    Course     │     │    Lesson     │     │     Quiz      │                  │
│  ├───────────────┤     ├───────────────┤     ├───────────────┤                  │
│  │ id            │     │ id            │     │ id            │                  │
│  │ tenant_id     │     │ course_id     │◄────│ lesson_id     │                  │
│  │ title         │◄────│ title         │     │ title         │                  │
│  │ slug          │     │ content       │     │ description   │                  │
│  │ description   │     │ video_url     │     │ pass_score    │                  │
│  │ thumbnail     │     │ duration_min  │     │ time_limit    │                  │
│  │ instructor_id │     │ order         │     │ attempts      │                  │
│  │ category_id   │     │ is_free       │     │ is_required   │                  │
│  │ level         │     │ attachments   │     └───────────────┘                  │
│  │ duration_hrs  │     └───────────────┘            │                           │
│  │ is_published  │                                  ▼                           │
│  │ is_mandatory  │                          ┌───────────────┐                   │
│  │ cert_template │                          │   Question    │                   │
│  └───────────────┘                          ├───────────────┤                   │
│         │                                   │ id            │                   │
│         ▼                                   │ quiz_id       │                   │
│  ┌───────────────┐     ┌───────────────┐    │ type          │                   │
│  │  Enrollment   │     │   Progress    │    │ question      │                   │
│  ├───────────────┤     ├───────────────┤    │ options (JSON)│                   │
│  │ id            │     │ id            │    │ correct_answer│                   │
│  │ course_id     │     │ enrollment_id │    │ points        │                   │
│  │ user_id       │◄────│ lesson_id     │    │ explanation   │                   │
│  │ enrolled_at   │     │ status        │    │ order         │                   │
│  │ completed_at  │     │ started_at    │    └───────────────┘                   │
│  │ progress_pct  │     │ completed_at  │                                        │
│  │ certificate_id│     │ time_spent    │                                        │
│  └───────────────┘     └───────────────┘                                        │
│                                                                                  │
│  ┌───────────────┐     ┌───────────────┐                                        │
│  │  Certificate  │     │    Badge      │                                        │
│  ├───────────────┤     ├───────────────┤                                        │
│  │ id            │     │ id            │                                        │
│  │ enrollment_id │     │ tenant_id     │                                        │
│  │ certificate_no│     │ name          │                                        │
│  │ issued_at     │     │ description   │                                        │
│  │ valid_until   │     │ icon          │                                        │
│  │ pdf_path      │     │ criteria      │                                        │
│  │ verify_code   │     │ points        │                                        │
│  └───────────────┘     └───────────────┘                                        │
│                                                                                  │
│  LEARNING PATH:                                                                  │
│  ──────────────                                                                  │
│                                                                                  │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │                                                                         │    │
│  │   Onboarding Path (Required for all new employees)                      │    │
│  │   ════════════════════════════════════════════════                      │    │
│  │                                                                         │    │
│  │   ┌─────────┐   ┌─────────┐   ┌─────────┐   ┌─────────┐   ┌─────────┐  │    │
│  │   │Company  │──►│Security │──►│ Tools   │──►│  Team   │──►│  Final  │  │    │
│  │   │Overview │   │Training │   │Training │   │ Intro   │   │  Quiz   │  │    │
│  │   │  30min  │   │  45min  │   │  60min  │   │  30min  │   │  20min  │  │    │
│  │   └─────────┘   └─────────┘   └─────────┘   └─────────┘   └─────────┘  │    │
│  │       │              │             │             │             │       │    │
│  │       ▼              ▼             ▼             ▼             ▼       │    │
│  │      🏅            🏅            🏅            🏅            📜      │    │
│  │    Badge 1       Badge 2       Badge 3       Badge 4      Certificate │    │
│  │                                                                         │    │
│  └─────────────────────────────────────────────────────────────────────────┘    │
│                                                                                  │
│  CERTIFICATE TEMPLATE:                                                           │
│  ─────────────────────                                                           │
│                                                                                  │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │  ╔═══════════════════════════════════════════════════════════════════╗  │    │
│  │  ║                                                                   ║  │    │
│  │  ║              🎓 CERTIFICATE OF COMPLETION 🎓                      ║  │    │
│  │  ║                                                                   ║  │    │
│  │  ║                      This certifies that                         ║  │    │
│  │  ║                                                                   ║  │    │
│  │  ║                     {{ employee_name }}                          ║  │    │
│  │  ║                                                                   ║  │    │
│  │  ║               has successfully completed the course              ║  │    │
│  │  ║                                                                   ║  │    │
│  │  ║                     {{ course_name }}                            ║  │    │
│  │  ║                                                                   ║  │    │
│  │  ║             with a score of {{ score }}%                         ║  │    │
│  │  ║                                                                   ║  │    │
│  │  ║   Issued: {{ date }}              Certificate: {{ cert_no }}     ║  │    │
│  │  ║                                                                   ║  │    │
│  │  ║   ┌─────────┐                                                    ║  │    │
│  │  ║   │ QR Code │  Verify: https://openbiz.local/verify/{{ code }}   ║  │    │
│  │  ║   └─────────┘                                                    ║  │    │
│  │  ║                                                                   ║  │    │
│  │  ╚═══════════════════════════════════════════════════════════════════╝  │    │
│  └─────────────────────────────────────────────────────────────────────────┘    │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 4.6 Shop Module

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              SHOP MODULE                                         │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  ENTITIES:                                                                       │
│  ─────────                                                                       │
│                                                                                  │
│  ┌───────────────┐     ┌───────────────┐     ┌───────────────┐                  │
│  │ProductCategory│     │   Product     │     │  ProductImage │                  │
│  ├───────────────┤     ├───────────────┤     ├───────────────┤                  │
│  │ id            │     │ id            │     │ id            │                  │
│  │ tenant_id     │     │ tenant_id     │     │ product_id    │                  │
│  │ name          │◄────│ category_id   │◄────│ path          │                  │
│  │ slug          │     │ sku           │     │ alt_text      │                  │
│  │ parent_id     │     │ name          │     │ order         │                  │
│  │ image         │     │ slug          │     │ is_primary    │                  │
│  └───────────────┘     │ description   │     └───────────────┘                  │
│                        │ price         │                                        │
│                        │ compare_price │     ┌───────────────┐                  │
│                        │ cost          │     │   Inventory   │                  │
│                        │ stock         │     ├───────────────┤                  │
│                        │ track_stock   │     │ id            │                  │
│                        │ weight        │     │ product_id    │                  │
│                        │ is_active     │◄────│ warehouse_id  │                  │
│                        │ is_featured   │     │ quantity      │                  │
│                        └───────────────┘     │ reserved      │                  │
│                                              │ reorder_point │                  │
│  ┌───────────────┐     ┌───────────────┐     └───────────────┘                  │
│  │    Order      │     │   OrderItem   │                                        │
│  ├───────────────┤     ├───────────────┤                                        │
│  │ id            │     │ id            │                                        │
│  │ tenant_id     │     │ order_id      │                                        │
│  │ order_number  │◄────│ product_id    │                                        │
│  │ customer_id   │     │ quantity      │                                        │
│  │ status        │     │ price         │                                        │
│  │ subtotal      │     │ total         │                                        │
│  │ tax           │     │ tax           │                                        │
│  │ shipping      │     └───────────────┘                                        │
│  │ discount      │                                                              │
│  │ total         │     ┌───────────────┐                                        │
│  │ payment_method│     │    Invoice    │                                        │
│  │ payment_status│     ├───────────────┤                                        │
│  │ shipping_addr │     │ id            │                                        │
│  │ billing_addr  │◄────│ order_id      │                                        │
│  │ notes         │     │ invoice_no    │                                        │
│  │ shipped_at    │     │ issued_at     │                                        │
│  │ delivered_at  │     │ due_at        │                                        │
│  └───────────────┘     │ paid_at       │                                        │
│                        │ pdf_path      │                                        │
│                        │ lexware_id    │ ◄── Lexware Office Sync              │
│                        └───────────────┘                                        │
│                                                                                  │
│  ORDER FLOW:                                                                     │
│  ───────────                                                                     │
│                                                                                  │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │                                                                         │    │
│  │  ┌────────┐   ┌─────────┐   ┌─────────┐   ┌─────────┐   ┌────────┐    │    │
│  │  │  Cart  │──►│Checkout │──►│ Payment │──►│ Process │──►│ Ship   │    │    │
│  │  │        │   │         │   │         │   │         │   │        │    │    │
│  │  └────────┘   └─────────┘   └────┬────┘   └────┬────┘   └───┬────┘    │    │
│  │                                  │             │             │         │    │
│  │                                  ▼             ▼             ▼         │    │
│  │                            ┌──────────┐  ┌──────────┐  ┌──────────┐   │    │
│  │                            │ Stripe   │  │ Create   │  │ Tracking │   │    │
│  │                            │ PayPal   │  │ Invoice  │  │ Email    │   │    │
│  │                            │ Bank     │  │ (Lexware)│  │ Update   │   │    │
│  │                            └──────────┘  └──────────┘  └──────────┘   │    │
│  │                                                                         │    │
│  └─────────────────────────────────────────────────────────────────────────┘    │
│                                                                                  │
│  LEXWARE OFFICE INTEGRATION:                                                     │
│  ───────────────────────────                                                     │
│                                                                                  │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │                                                                         │    │
│  │  OpenBiz                              Lexware Office                    │    │
│  │  ┌──────────────┐                     ┌──────────────┐                 │    │
│  │  │   Order      │                     │   Invoice    │                 │    │
│  │  │   Created    │ ──── Webhook ────►  │   Created    │                 │    │
│  │  └──────────────┘                     └──────────────┘                 │    │
│  │         │                                    │                         │    │
│  │         │         REST API                   │                         │    │
│  │         │         POST /invoices             │                         │    │
│  │         │         ─────────────►             │                         │    │
│  │         │                                    │                         │    │
│  │         │         Response                   │                         │    │
│  │         │         ◄─────────────             │                         │    │
│  │         │         {invoice_id, pdf_url}      │                         │    │
│  │         ▼                                    ▼                         │    │
│  │  ┌──────────────┐                     ┌──────────────┐                 │    │
│  │  │   Order      │                     │   Payment    │                 │    │
│  │  │   Invoiced   │ ◄─── Webhook ─────  │   Received   │                 │    │
│  │  └──────────────┘                     └──────────────┘                 │    │
│  │                                                                         │    │
│  └─────────────────────────────────────────────────────────────────────────┘    │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 5. Datenbank-Design

### 5.1 ERD Overview

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                        ENTITY RELATIONSHIP DIAGRAM                               │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│                              ┌─────────────┐                                    │
│                              │   tenants   │                                    │
│                              └──────┬──────┘                                    │
│                                     │                                            │
│         ┌───────────────────────────┼───────────────────────────┐               │
│         │                           │                           │               │
│         ▼                           ▼                           ▼               │
│  ┌─────────────┐             ┌─────────────┐             ┌─────────────┐        │
│  │    users    │             │ departments │             │  locations  │        │
│  └──────┬──────┘             └──────┬──────┘             └──────┬──────┘        │
│         │                           │                           │               │
│         │      ┌────────────────────┼────────────────────┐      │               │
│         │      │                    │                    │      │               │
│         ▼      ▼                    ▼                    ▼      ▼               │
│  ┌─────────────────┐         ┌─────────────┐         ┌─────────────┐           │
│  │    employees    │◄────────│  positions  │         │   assets    │           │
│  └────────┬────────┘         └─────────────┘         └──────┬──────┘           │
│           │                                                  │                  │
│     ┌─────┴─────┐                                     ┌──────┴──────┐          │
│     │           │                                     │             │          │
│     ▼           ▼                                     ▼             ▼          │
│ ┌────────┐ ┌──────────┐                       ┌────────────┐ ┌────────────┐    │
│ │  time  │ │  leave   │                       │  transfers │ │maintenance │    │
│ │entries │ │ requests │                       │            │ │            │    │
│ └────────┘ └──────────┘                       └────────────┘ └────────────┘    │
│                                                                                  │
│         ┌─────────────┐         ┌─────────────┐         ┌─────────────┐        │
│         │   courses   │────────►│   lessons   │────────►│   quizzes   │        │
│         └──────┬──────┘         └─────────────┘         └──────┬──────┘        │
│                │                                                │               │
│                ▼                                                ▼               │
│         ┌─────────────┐                                 ┌─────────────┐        │
│         │ enrollments │────────────────────────────────►│  progress   │        │
│         └──────┬──────┘                                 └─────────────┘        │
│                │                                                                │
│                ▼                                                                │
│         ┌─────────────┐                                                        │
│         │certificates │                                                        │
│         └─────────────┘                                                        │
│                                                                                  │
│         ┌─────────────┐         ┌─────────────┐         ┌─────────────┐        │
│         │  products   │────────►│   orders    │────────►│  invoices   │        │
│         └─────────────┘         └──────┬──────┘         └─────────────┘        │
│                                        │                                        │
│                                        ▼                                        │
│                                 ┌─────────────┐                                 │
│                                 │ order_items │                                 │
│                                 └─────────────┘                                 │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 5.2 Core Tables

```sql
-- ============================================
-- TENANT (Multi-Tenancy Root)
-- ============================================
CREATE TABLE tenants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    domain VARCHAR(255) NULL UNIQUE,
    logo_path VARCHAR(255) NULL,
    settings JSON NULL,
    plan ENUM('free', 'starter', 'business', 'enterprise') DEFAULT 'free',
    trial_ends_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_slug (slug),
    INDEX idx_domain (domain)
);

-- ============================================
-- USER
-- ============================================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    tenant_id BIGINT UNSIGNED NOT NULL,
    email VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    two_factor_secret TEXT NULL,
    two_factor_recovery_codes TEXT NULL,
    two_factor_confirmed_at TIMESTAMP NULL,
    avatar_path VARCHAR(255) NULL,
    locale VARCHAR(10) DEFAULT 'de',
    timezone VARCHAR(50) DEFAULT 'Europe/Berlin',
    is_active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    last_login_ip VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    UNIQUE KEY uk_tenant_email (tenant_id, email),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_email (email)
);

-- ============================================
-- ROLES & PERMISSIONS (Spatie Pattern)
-- ============================================
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NULL, -- NULL = global role
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) DEFAULT 'web',
    description TEXT NULL,
    is_system BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_tenant_name_guard (tenant_id, name, guard_name),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) DEFAULT 'web',
    group_name VARCHAR(255) NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_name_guard (name, guard_name)
);

CREATE TABLE model_has_roles (
    role_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    
    PRIMARY KEY (role_id, model_id, model_type),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    INDEX idx_model (model_type, model_id)
);

CREATE TABLE role_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    
    PRIMARY KEY (permission_id, role_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- ============================================
-- AUDIT LOG
-- ============================================
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    event ENUM('created', 'updated', 'deleted', 'restored', 'login', 'logout', 'other') NOT NULL,
    auditable_type VARCHAR(255) NOT NULL,
    auditable_id BIGINT UNSIGNED NOT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    url VARCHAR(2048) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(1024) NULL,
    tags JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_auditable (auditable_type, auditable_id),
    INDEX idx_user (user_id),
    INDEX idx_event (event),
    INDEX idx_created (created_at)
);
```

---

## 6. API-Spezifikation

### 6.1 API Architecture

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                            API ARCHITECTURE                                      │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│                              ┌─────────────────┐                                │
│                              │    API CLIENT   │                                │
│                              │  (Mobile, Web,  │                                │
│                              │   3rd Party)    │                                │
│                              └────────┬────────┘                                │
│                                       │                                          │
│                                       ▼                                          │
│                    ┌──────────────────────────────────┐                         │
│                    │         RATE LIMITER             │                         │
│                    │   (60 req/min default)           │                         │
│                    └───────────────┬──────────────────┘                         │
│                                    │                                             │
│                                    ▼                                             │
│                    ┌──────────────────────────────────┐                         │
│                    │        AUTHENTICATION            │                         │
│                    │   Bearer Token (Sanctum)         │                         │
│                    │   API Key (for M2M)              │                         │
│                    └───────────────┬──────────────────┘                         │
│                                    │                                             │
│              ┌─────────────────────┼─────────────────────┐                      │
│              │                     │                     │                      │
│              ▼                     ▼                     ▼                      │
│    ┌─────────────────┐   ┌─────────────────┐   ┌─────────────────┐             │
│    │    REST API     │   │    GraphQL      │   │    Webhooks     │             │
│    │   /api/v1/*     │   │    /graphql     │   │  (Outbound)     │             │
│    └────────┬────────┘   └────────┬────────┘   └────────┬────────┘             │
│             │                     │                     │                       │
│             └─────────────────────┼─────────────────────┘                       │
│                                   │                                              │
│                                   ▼                                              │
│                    ┌──────────────────────────────────┐                         │
│                    │        AUTHORIZATION             │                         │
│                    │   Policy-based (Laravel Gates)   │                         │
│                    └───────────────┬──────────────────┘                         │
│                                    │                                             │
│                                    ▼                                             │
│                    ┌──────────────────────────────────┐                         │
│                    │        TENANT SCOPE              │                         │
│                    │   (Automatic filtering)          │                         │
│                    └───────────────┬──────────────────┘                         │
│                                    │                                             │
│                                    ▼                                             │
│                    ┌──────────────────────────────────┐                         │
│                    │        BUSINESS LOGIC            │                         │
│                    │   (Services, Actions)            │                         │
│                    └──────────────────────────────────┘                         │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 6.2 REST API Endpoints

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           REST API ENDPOINTS                                     │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  Base URL: https://api.openbiz.local/v1                                         │
│                                                                                  │
│  ════════════════════════════════════════════════════════════════════════════   │
│  AUTHENTICATION                                                                  │
│  ════════════════════════════════════════════════════════════════════════════   │
│                                                                                  │
│  POST   /auth/login              Login with email/password                      │
│  POST   /auth/logout             Logout (revoke token)                          │
│  POST   /auth/register           Register new tenant + admin                    │
│  POST   /auth/forgot-password    Request password reset                         │
│  POST   /auth/reset-password     Reset password with token                      │
│  GET    /auth/me                 Get current user + tenant                      │
│  POST   /auth/2fa/enable         Enable 2FA                                     │
│  POST   /auth/2fa/verify         Verify 2FA code                                │
│                                                                                  │
│  ════════════════════════════════════════════════════════════════════════════   │
│  HR MODULE                                                                       │
│  ════════════════════════════════════════════════════════════════════════════   │
│                                                                                  │
│  # Employees                                                                     │
│  GET    /employees               List employees (paginated, filterable)         │
│  POST   /employees               Create employee                                │
│  GET    /employees/{id}          Get employee details                           │
│  PUT    /employees/{id}          Update employee                                │
│  DELETE /employees/{id}          Delete (soft) employee                         │
│  GET    /employees/{id}/documents  Get employee documents                       │
│  POST   /employees/{id}/documents  Upload document                              │
│                                                                                  │
│  # Time Tracking                                                                 │
│  GET    /time-entries            List time entries                              │
│  POST   /time-entries/clock-in   Clock in                                       │
│  POST   /time-entries/clock-out  Clock out                                      │
│  GET    /time-entries/today      Get today's entry for current user            │
│  GET    /time-entries/report     Generate time report                           │
│                                                                                  │
│  # Leave Management                                                              │
│  GET    /leave-requests          List leave requests                            │
│  POST   /leave-requests          Create leave request                           │
│  PUT    /leave-requests/{id}     Update leave request                           │
│  POST   /leave-requests/{id}/approve   Approve request                          │
│  POST   /leave-requests/{id}/reject    Reject request                           │
│  GET    /leave-balances          Get leave balances for employees               │
│                                                                                  │
│  ════════════════════════════════════════════════════════════════════════════   │
│  ASSET MODULE                                                                    │
│  ════════════════════════════════════════════════════════════════════════════   │
│                                                                                  │
│  GET    /assets                  List assets                                    │
│  POST   /assets                  Create asset                                   │
│  GET    /assets/{id}             Get asset details                              │
│  PUT    /assets/{id}             Update asset                                   │
│  DELETE /assets/{id}             Delete asset                                   │
│  GET    /assets/scan/{uuid}      Scan asset by QR UUID                         │
│  POST   /assets/{id}/transfer    Transfer asset                                │
│  GET    /assets/{id}/history     Get asset history                             │
│  POST   /assets/{id}/label       Generate label PDF                            │
│  POST   /assets/labels/batch     Generate multiple labels                      │
│                                                                                  │
│  # Maintenance                                                                   │
│  GET    /maintenance             List maintenance records                       │
│  POST   /maintenance             Schedule maintenance                           │
│  PUT    /maintenance/{id}        Update/complete maintenance                    │
│  GET    /maintenance/upcoming    Get upcoming maintenance                       │
│                                                                                  │
│  ════════════════════════════════════════════════════════════════════════════   │
│  LMS MODULE                                                                      │
│  ════════════════════════════════════════════════════════════════════════════   │
│                                                                                  │
│  GET    /courses                 List courses                                   │
│  POST   /courses                 Create course                                  │
│  GET    /courses/{id}            Get course details                             │
│  PUT    /courses/{id}            Update course                                  │
│  POST   /courses/{id}/publish    Publish course                                 │
│  POST   /courses/{id}/enroll     Enroll user                                    │
│                                                                                  │
│  GET    /enrollments             Get user enrollments                           │
│  GET    /enrollments/{id}        Get enrollment details                         │
│  PUT    /enrollments/{id}/progress  Update progress                             │
│                                                                                  │
│  POST   /quizzes/{id}/submit     Submit quiz answers                           │
│  GET    /quizzes/{id}/results    Get quiz results                              │
│                                                                                  │
│  GET    /certificates/{id}       Get certificate                               │
│  GET    /certificates/verify/{code}  Verify certificate                        │
│                                                                                  │
│  ════════════════════════════════════════════════════════════════════════════   │
│  SHOP MODULE                                                                     │
│  ════════════════════════════════════════════════════════════════════════════   │
│                                                                                  │
│  GET    /products                List products                                  │
│  POST   /products                Create product                                 │
│  GET    /products/{id}           Get product details                            │
│  PUT    /products/{id}           Update product                                 │
│  DELETE /products/{id}           Delete product                                 │
│  PUT    /products/{id}/stock     Update stock                                   │
│                                                                                  │
│  GET    /orders                  List orders                                    │
│  POST   /orders                  Create order                                   │
│  GET    /orders/{id}             Get order details                              │
│  PUT    /orders/{id}/status      Update order status                           │
│  POST   /orders/{id}/invoice     Generate invoice                               │
│                                                                                  │
│  ════════════════════════════════════════════════════════════════════════════   │
│  WEBHOOKS (Outbound)                                                             │
│  ════════════════════════════════════════════════════════════════════════════   │
│                                                                                  │
│  GET    /webhooks                List webhook endpoints                         │
│  POST   /webhooks                Create webhook endpoint                        │
│  PUT    /webhooks/{id}           Update webhook                                 │
│  DELETE /webhooks/{id}           Delete webhook                                 │
│  GET    /webhooks/{id}/deliveries  Get delivery history                        │
│  POST   /webhooks/{id}/test      Send test payload                             │
│                                                                                  │
│  Available Events:                                                               │
│  • employee.created, employee.updated, employee.deleted                         │
│  • asset.created, asset.transferred, asset.maintenance_due                      │
│  • course.completed, certificate.issued                                         │
│  • order.created, order.paid, order.shipped                                     │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 6.3 API Response Format

```json
// Success Response
{
  "success": true,
  "data": {
    "id": 123,
    "type": "employee",
    "attributes": {
      "first_name": "Max",
      "last_name": "Mustermann",
      "email": "max@example.com"
    },
    "relationships": {
      "department": {
        "id": 5,
        "name": "Engineering"
      }
    }
  },
  "meta": {
    "timestamp": "2024-01-15T10:30:00Z",
    "request_id": "req_abc123"
  }
}

// Paginated Response
{
  "success": true,
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 25,
    "total": 127,
    "last_page": 6
  },
  "links": {
    "first": "/api/v1/employees?page=1",
    "last": "/api/v1/employees?page=6",
    "prev": null,
    "next": "/api/v1/employees?page=2"
  }
}

// Error Response
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "details": {
      "email": ["The email field is required."],
      "salary": ["The salary must be a number."]
    }
  },
  "meta": {
    "timestamp": "2024-01-15T10:30:00Z",
    "request_id": "req_abc123"
  }
}
```

---

## 7. Frontend-Design

### 7.1 Design System

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                            DESIGN SYSTEM                                         │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  COLOR PALETTE                                                                   │
│  ═════════════                                                                   │
│                                                                                  │
│  Primary:     #3B82F6 (Blue 500)       ████████████                             │
│  Secondary:   #8B5CF6 (Violet 500)     ████████████                             │
│  Success:     #10B981 (Emerald 500)    ████████████                             │
│  Warning:     #F59E0B (Amber 500)      ████████████                             │
│  Danger:      #EF4444 (Red 500)        ████████████                             │
│  Info:        #06B6D4 (Cyan 500)       ████████████                             │
│                                                                                  │
│  Neutrals:                                                                       │
│  Gray 50:     #F9FAFB  ░░░░░░░░░░░░  Background                                │
│  Gray 100:    #F3F4F6  ▒▒▒▒▒▒▒▒▒▒▒▒  Card Background                           │
│  Gray 200:    #E5E7EB  ▓▓▓▓▓▓▓▓▓▓▓▓  Border                                    │
│  Gray 500:    #6B7280  ████████████  Muted Text                                │
│  Gray 900:    #111827  ████████████  Primary Text                              │
│                                                                                  │
│  TYPOGRAPHY                                                                      │
│  ══════════                                                                      │
│                                                                                  │
│  Font Family: Inter (Google Fonts)                                              │
│                                                                                  │
│  H1: 2.25rem (36px) / Bold     │  Used for page titles                         │
│  H2: 1.875rem (30px) / Semibold │  Used for section headers                    │
│  H3: 1.5rem (24px) / Semibold  │  Used for card titles                         │
│  H4: 1.25rem (20px) / Medium   │  Used for subsections                         │
│  Body: 1rem (16px) / Normal    │  Default text                                 │
│  Small: 0.875rem (14px) / Normal│  Secondary text, labels                      │
│  Tiny: 0.75rem (12px) / Normal │  Badges, timestamps                           │
│                                                                                  │
│  SPACING                                                                         │
│  ═══════                                                                         │
│                                                                                  │
│  Base unit: 4px (Tailwind default)                                              │
│                                                                                  │
│  xs:  4px  (p-1)    │  Tight spacing                                           │
│  sm:  8px  (p-2)    │  Compact elements                                        │
│  md:  16px (p-4)    │  Default padding                                         │
│  lg:  24px (p-6)    │  Section spacing                                         │
│  xl:  32px (p-8)    │  Large gaps                                              │
│  2xl: 48px (p-12)   │  Page sections                                           │
│                                                                                  │
│  SHADOWS                                                                         │
│  ═══════                                                                         │
│                                                                                  │
│  sm:  0 1px 2px rgba(0,0,0,0.05)           Subtle lift                         │
│  md:  0 4px 6px -1px rgba(0,0,0,0.1)       Cards, dropdowns                    │
│  lg:  0 10px 15px -3px rgba(0,0,0,0.1)     Modals                              │
│  xl:  0 20px 25px -5px rgba(0,0,0,0.1)     Popovers                            │
│                                                                                  │
│  BORDER RADIUS                                                                   │
│  ═════════════                                                                   │
│                                                                                  │
│  sm:  0.125rem (2px)   │  Buttons, inputs                                      │
│  md:  0.375rem (6px)   │  Cards, default                                       │
│  lg:  0.5rem (8px)     │  Large cards                                          │
│  xl:  0.75rem (12px)   │  Modals                                               │
│  full: 9999px          │  Pills, avatars                                       │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 7.2 Layout Structure

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                             MAIN LAYOUT                                          │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  ┌───────────────────────────────────────────────────────────────────────────┐  │
│  │                            TOP NAVBAR (64px)                               │  │
│  │  ┌────────┐                                        ┌────┐ ┌────┐ ┌──────┐ │  │
│  │  │ LOGO   │  [Search...]                          │ 🔔 │ │ ❓ │ │Avatar│ │  │
│  │  └────────┘                                        └────┘ └────┘ └──────┘ │  │
│  └───────────────────────────────────────────────────────────────────────────┘  │
│  ┌────────────────┬──────────────────────────────────────────────────────────┐  │
│  │                │                                                          │  │
│  │   SIDEBAR      │                    MAIN CONTENT                          │  │
│  │   (256px)      │                                                          │  │
│  │                │  ┌────────────────────────────────────────────────────┐  │  │
│  │  ┌──────────┐  │  │  BREADCRUMB                                        │  │  │
│  │  │ Dashboard│  │  │  Home > HR > Employees                             │  │  │
│  │  └──────────┘  │  └────────────────────────────────────────────────────┘  │  │
│  │                │                                                          │  │
│  │  HR            │  ┌────────────────────────────────────────────────────┐  │  │
│  │  ├─ Employees  │  │  PAGE HEADER                                       │  │  │
│  │  ├─ Time       │  │  ┌──────────────────────────┐  ┌────────────────┐  │  │  │
│  │  ├─ Leave      │  │  │ Employees                │  │ + Add Employee │  │  │  │
│  │  └─ Documents  │  │  │ Manage your team         │  └────────────────┘  │  │  │
│  │                │  │  └──────────────────────────┘                      │  │  │
│  │  Assets        │  └────────────────────────────────────────────────────┘  │  │
│  │  ├─ Inventory  │                                                          │  │
│  │  ├─ Scanner    │  ┌────────────────────────────────────────────────────┐  │  │
│  │  └─ Maintenance│  │  CONTENT AREA                                      │  │  │
│  │                │  │                                                    │  │  │
│  │  Learning      │  │  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐  │  │  │
│  │  ├─ Courses    │  │  │ STAT 1  │ │ STAT 2  │ │ STAT 3  │ │ STAT 4  │  │  │  │
│  │  ├─ My Learning│  │  └─────────┘ └─────────┘ └─────────┘ └─────────┘  │  │  │
│  │  └─ Certs      │  │                                                    │  │  │
│  │                │  │  ┌──────────────────────────────────────────────┐  │  │  │
│  │  Shop          │  │  │                                              │  │  │  │
│  │  ├─ Products   │  │  │              DATA TABLE                      │  │  │  │
│  │  ├─ Orders     │  │  │                                              │  │  │  │
│  │  └─ Invoices   │  │  │                                              │  │  │  │
│  │                │  │  │                                              │  │  │  │
│  │  ─────────────  │  │  │                                              │  │  │  │
│  │                │  │  └──────────────────────────────────────────────┘  │  │  │
│  │  Settings      │  │                                                    │  │  │
│  │  API Docs      │  └────────────────────────────────────────────────────┘  │  │
│  │                │                                                          │  │
│  └────────────────┴──────────────────────────────────────────────────────────┘  │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 7.3 Component Library

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          COMPONENT LIBRARY                                       │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  BUTTONS                                                                         │
│  ═══════                                                                         │
│                                                                                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐            │
│  │   Primary   │  │  Secondary  │  │   Danger    │  │   Ghost     │            │
│  │ ████████████│  │ ░░░░░░░░░░░░│  │ ████████████│  │             │            │
│  │  + Create   │  │   Cancel    │  │   Delete    │  │   Learn →   │            │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘            │
│                                                                                  │
│  Button Sizes: sm (32px) | md (40px) | lg (48px)                               │
│                                                                                  │
│  INPUTS                                                                          │
│  ══════                                                                          │
│                                                                                  │
│  ┌─────────────────────────────────────────────┐                               │
│  │ Email Address                               │                               │
│  │ ┌─────────────────────────────────────────┐ │                               │
│  │ │ 📧  john@example.com                    │ │                               │
│  │ └─────────────────────────────────────────┘ │                               │
│  │ Enter your work email                       │                               │
│  └─────────────────────────────────────────────┘                               │
│                                                                                  │
│  States: Default | Focus | Error | Disabled                                     │
│                                                                                  │
│  CARDS                                                                           │
│  ═════                                                                           │
│                                                                                  │
│  ┌─────────────────────────────────────────────┐                               │
│  │ ┌─────────────────────────────────────────┐ │                               │
│  │ │ Card Header                    [•••]   │ │                               │
│  │ └─────────────────────────────────────────┘ │                               │
│  │                                             │                               │
│  │  Card content goes here. This is the main  │                               │
│  │  body of the card component.               │                               │
│  │                                             │                               │
│  │ ┌─────────────────────────────────────────┐ │                               │
│  │ │ [Cancel]              [Save Changes]   │ │                               │
│  │ └─────────────────────────────────────────┘ │                               │
│  └─────────────────────────────────────────────┘                               │
│                                                                                  │
│  BADGES                                                                          │
│  ══════                                                                          │
│                                                                                  │
│  ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐                        │
│  │ Active │ │Pending │ │Approved│ │Rejected│ │  Draft │                        │
│  │  🟢    │ │  🟡    │ │  🟢    │ │  🔴    │ │  ⚪    │                        │
│  └────────┘ └────────┘ └────────┘ └────────┘ └────────┘                        │
│                                                                                  │
│  TABLES                                                                          │
│  ══════                                                                          │
│                                                                                  │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │ ☐ │ Name          │ Department  │ Status   │ Hired      │ Actions     │   │
│  ├───┼───────────────┼─────────────┼──────────┼────────────┼─────────────┤   │
│  │ ☐ │ 👤 Max Müller │ Engineering │ 🟢 Active│ 2023-01-15 │ [👁] [✏] [🗑]│   │
│  │ ☐ │ 👤 Anna Schmidt│ Marketing   │ 🟡 Leave │ 2022-06-01 │ [👁] [✏] [🗑]│   │
│  │ ☐ │ 👤 Tom Becker │ Sales       │ 🟢 Active│ 2024-03-20 │ [👁] [✏] [🗑]│   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│  │ Showing 1-10 of 127                    │ ◄ │ 1 │ 2 │ 3 │ ... │ 13 │ ► │    │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                  │
│  MODALS                                                                          │
│  ══════                                                                          │
│                                                                                  │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░│   │
│  │░░░░░░░░░░░░░┌───────────────────────────────────────┐░░░░░░░░░░░░░░░░░│   │
│  │░░░░░░░░░░░░░│  Create Employee                  [X] │░░░░░░░░░░░░░░░░░│   │
│  │░░░░░░░░░░░░░├───────────────────────────────────────┤░░░░░░░░░░░░░░░░░│   │
│  │░░░░░░░░░░░░░│                                       │░░░░░░░░░░░░░░░░░│   │
│  │░░░░░░░░░░░░░│  First Name: [________________]      │░░░░░░░░░░░░░░░░░│   │
│  │░░░░░░░░░░░░░│  Last Name:  [________________]      │░░░░░░░░░░░░░░░░░│   │
│  │░░░░░░░░░░░░░│  Email:      [________________]      │░░░░░░░░░░░░░░░░░│   │
│  │░░░░░░░░░░░░░│  Department: [▼ Select...     ]      │░░░░░░░░░░░░░░░░░│   │
│  │░░░░░░░░░░░░░│                                       │░░░░░░░░░░░░░░░░░│   │
│  │░░░░░░░░░░░░░├───────────────────────────────────────┤░░░░░░░░░░░░░░░░░│   │
│  │░░░░░░░░░░░░░│         [Cancel]  [Create Employee]  │░░░░░░░░░░░░░░░░░│   │
│  │░░░░░░░░░░░░░└───────────────────────────────────────┘░░░░░░░░░░░░░░░░░│   │
│  │░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░│   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 8. Workflow Engine

### 8.1 Architecture

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                         WORKFLOW ENGINE ARCHITECTURE                             │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │                        WORKFLOW DEFINITION                               │    │
│  │                                                                         │    │
│  │  {                                                                      │    │
│  │    "id": "wf_employee_onboarding",                                     │    │
│  │    "name": "Employee Onboarding",                                      │    │
│  │    "trigger": {                                                        │    │
│  │      "type": "event",                                                  │    │
│  │      "event": "employee.created"                                       │    │
│  │    },                                                                  │    │
│  │    "nodes": [                                                          │    │
│  │      {"id": "n1", "type": "action", "action": "send_email", ...},     │    │
│  │      {"id": "n2", "type": "condition", "condition": "...", ...},      │    │
│  │      {"id": "n3", "type": "action", "action": "create_asset", ...}    │    │
│  │    ],                                                                  │    │
│  │    "edges": [                                                          │    │
│  │      {"from": "trigger", "to": "n1"},                                  │    │
│  │      {"from": "n1", "to": "n2"},                                       │    │
│  │      {"from": "n2", "to": "n3", "condition": "true"}                  │    │
│  │    ]                                                                   │    │
│  │  }                                                                      │    │
│  │                                                                         │    │
│  └─────────────────────────────────────────────────────────────────────────┘    │
│                                                                                  │
│  TRIGGER TYPES:                                                                  │
│  ══════════════                                                                  │
│                                                                                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐            │
│  │   EVENT     │  │  SCHEDULE   │  │   WEBHOOK   │  │   MANUAL    │            │
│  │ ─────────── │  │ ─────────── │  │ ─────────── │  │ ─────────── │            │
│  │ employee.*  │  │ 0 9 * * MON │  │ POST /hook/ │  │ Button/API  │            │
│  │ asset.*     │  │ (cron expr) │  │ {payload}   │  │ trigger     │            │
│  │ order.*     │  │             │  │             │  │             │            │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘            │
│                                                                                  │
│  NODE TYPES:                                                                     │
│  ═══════════                                                                     │
│                                                                                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐            │
│  │   ACTION    │  │  CONDITION  │  │    DELAY    │  │    LOOP     │            │
│  │ ─────────── │  │ ─────────── │  │ ─────────── │  │ ─────────── │            │
│  │ send_email  │  │ if/else     │  │ wait 1h     │  │ for each    │            │
│  │ http_req    │  │ switch      │  │ wait until  │  │ while       │            │
│  │ create_*    │  │             │  │             │  │             │            │
│  │ update_*    │  │             │  │             │  │             │            │
│  │ notify      │  │             │  │             │  │             │            │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘            │
│                                                                                  │
│  EXECUTION:                                                                      │
│  ══════════                                                                      │
│                                                                                  │
│  ┌─────────┐    ┌─────────┐    ┌─────────┐    ┌─────────┐    ┌─────────┐       │
│  │ Event   │───►│ Queue   │───►│ Worker  │───►│ Execute │───►│   Log   │       │
│  │ Fired   │    │ (Redis) │    │ Process │    │  Nodes  │    │ Result  │       │
│  └─────────┘    └─────────┘    └─────────┘    └─────────┘    └─────────┘       │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 8.2 Available Actions

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           WORKFLOW ACTIONS                                       │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  COMMUNICATION                                                                   │
│  ═════════════                                                                   │
│  • send_email         - Send email (template-based)                             │
│  • send_notification  - In-app notification                                     │
│  • send_slack         - Slack message                                           │
│  • send_teams         - Microsoft Teams message                                 │
│  • send_sms           - SMS via Twilio                                          │
│                                                                                  │
│  HR ACTIONS                                                                      │
│  ══════════                                                                      │
│  • create_employee    - Create new employee                                     │
│  • update_employee    - Update employee data                                    │
│  • assign_manager     - Assign manager                                          │
│  • assign_department  - Assign to department                                    │
│  • create_leave       - Create leave request                                    │
│  • approve_leave      - Auto-approve leave                                      │
│                                                                                  │
│  ASSET ACTIONS                                                                   │
│  ═════════════                                                                   │
│  • create_asset       - Create new asset                                        │
│  • assign_asset       - Assign to employee                                      │
│  • transfer_asset     - Transfer between locations                              │
│  • schedule_maintenance - Schedule maintenance                                  │
│                                                                                  │
│  LMS ACTIONS                                                                     │
│  ═══════════                                                                     │
│  • enroll_course      - Enroll user in course                                  │
│  • assign_training    - Assign mandatory training                               │
│  • issue_certificate  - Generate certificate                                    │
│  • award_badge        - Award achievement badge                                 │
│                                                                                  │
│  SHOP ACTIONS                                                                    │
│  ════════════                                                                    │
│  • create_order       - Create order                                            │
│  • update_order       - Update order status                                     │
│  • generate_invoice   - Generate invoice (+ Lexware sync)                       │
│  • process_refund     - Process refund                                          │
│                                                                                  │
│  INTEGRATION ACTIONS                                                             │
│  ═══════════════════                                                             │
│  • http_request       - Generic HTTP request                                    │
│  • webhook_call       - Call external webhook                                   │
│  • lexware_sync       - Sync with Lexware Office                                │
│  • ai_generate        - Call AI (Claude/OpenAI)                                 │
│                                                                                  │
│  UTILITY ACTIONS                                                                 │
│  ════════════════                                                                │
│  • set_variable       - Set workflow variable                                   │
│  • transform_data     - Transform/map data                                      │
│  • log_message        - Log to audit                                            │
│  • create_task        - Create internal task                                    │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 9. AI Integration

### 9.1 Architecture

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          AI INTEGRATION                                          │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │                         AI SERVICE LAYER                                 │    │
│  │                                                                         │    │
│  │  ┌───────────────────────────────────────────────────────────────────┐  │    │
│  │  │                    AI Service Interface                           │  │    │
│  │  │                                                                   │  │    │
│  │  │  • chat(messages[], options)      - Conversational AI            │  │    │
│  │  │  • complete(prompt, options)      - Text completion              │  │    │
│  │  │  • embed(text)                    - Generate embeddings          │  │    │
│  │  │  • analyze(content, task)         - Content analysis             │  │    │
│  │  └───────────────────────────────────────────────────────────────────┘  │    │
│  │                              │                                          │    │
│  │         ┌────────────────────┼────────────────────┐                     │    │
│  │         │                    │                    │                     │    │
│  │         ▼                    ▼                    ▼                     │    │
│  │  ┌─────────────┐      ┌─────────────┐      ┌─────────────┐             │    │
│  │  │   Claude    │      │   OpenAI    │      │   Ollama    │             │    │
│  │  │   Driver    │      │   Driver    │      │   Driver    │             │    │
│  │  │             │      │             │      │  (Local)    │             │    │
│  │  └─────────────┘      └─────────────┘      └─────────────┘             │    │
│  │                                                                         │    │
│  └─────────────────────────────────────────────────────────────────────────┘    │
│                                                                                  │
│  USE CASES:                                                                      │
│  ══════════                                                                      │
│                                                                                  │
│  1. AI ASSISTANT (Chat Interface)                                               │
│     ┌─────────────────────────────────────────────────────────────────────┐    │
│     │                                                                     │    │
│     │  User: "Create an onboarding workflow for new developers"           │    │
│     │                                                                     │    │
│     │  AI: "I'll create a workflow with these steps:                      │    │
│     │       1. Welcome email with credentials                             │    │
│     │       2. Assign laptop from IT inventory                            │    │
│     │       3. Enroll in Security Training (mandatory)                    │    │
│     │       4. Enroll in Git Workflow course                              │    │
│     │       5. Schedule 1:1 with team lead                                │    │
│     │       6. Add to Slack channels                                      │    │
│     │                                                                     │    │
│     │       [Preview Workflow] [Create Now]"                              │    │
│     │                                                                     │    │
│     └─────────────────────────────────────────────────────────────────────┘    │
│                                                                                  │
│  2. SMART QUIZ GENERATION (LMS)                                                 │
│     ┌─────────────────────────────────────────────────────────────────────┐    │
│     │                                                                     │    │
│     │  Input: Lesson content (text, video transcript)                     │    │
│     │                                                                     │    │
│     │  Output: {                                                          │    │
│     │    "questions": [                                                   │    │
│     │      {                                                              │    │
│     │        "type": "multiple_choice",                                   │    │
│     │        "question": "What is the primary purpose of...",            │    │
│     │        "options": ["A", "B", "C", "D"],                            │    │
│     │        "correct": "B",                                              │    │
│     │        "explanation": "..."                                         │    │
│     │      }                                                              │    │
│     │    ]                                                                │    │
│     │  }                                                                  │    │
│     │                                                                     │    │
│     └─────────────────────────────────────────────────────────────────────┘    │
│                                                                                  │
│  3. DOCUMENT ANALYSIS (HR)                                                       │
│     ┌─────────────────────────────────────────────────────────────────────┐    │
│     │                                                                     │    │
│     │  Input: Uploaded CV/Resume (PDF)                                    │    │
│     │                                                                     │    │
│     │  Output: {                                                          │    │
│     │    "name": "Max Mustermann",                                        │    │
│     │    "email": "max@example.com",                                      │    │
│     │    "skills": ["PHP", "Laravel", "Docker"],                         │    │
│     │    "experience_years": 5,                                           │    │
│     │    "education": [...],                                              │    │
│     │    "summary": "Senior developer with..."                            │    │
│     │  }                                                                  │    │
│     │                                                                     │    │
│     └─────────────────────────────────────────────────────────────────────┘    │
│                                                                                  │
│  4. SMART SEARCH (All Modules)                                                   │
│     ┌─────────────────────────────────────────────────────────────────────┐    │
│     │                                                                     │    │
│     │  Query: "Find all laptops assigned to marketing that need          │    │
│     │          maintenance in the next 30 days"                           │    │
│     │                                                                     │    │
│     │  → Converted to structured query                                    │    │
│     │  → Executed against database                                        │    │
│     │  → Results returned with AI summary                                 │    │
│     │                                                                     │    │
│     └─────────────────────────────────────────────────────────────────────┘    │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 10. Sicherheit

### 10.1 Security Architecture

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          SECURITY ARCHITECTURE                                   │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  AUTHENTICATION                                                                  │
│  ══════════════                                                                  │
│                                                                                  │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │                                                                         │    │
│  │  1. Session-based (Web)                                                 │    │
│  │     └─► Laravel Sanctum with encrypted cookies                          │    │
│  │     └─► CSRF protection on all forms                                    │    │
│  │     └─► Session timeout: 2 hours                                        │    │
│  │                                                                         │    │
│  │  2. Token-based (API)                                                   │    │
│  │     └─► Bearer tokens (Sanctum)                                         │    │
│  │     └─► Expiry: 1 year (configurable)                                   │    │
│  │     └─► Scoped abilities (read, write, admin)                           │    │
│  │                                                                         │    │
│  │  3. Two-Factor Authentication                                           │    │
│  │     └─► TOTP (Google Authenticator, Authy)                             │    │
│  │     └─► Backup codes (10 single-use)                                    │    │
│  │     └─► Enforced for admin roles                                        │    │
│  │                                                                         │    │
│  │  4. OAuth 2.0 / SAML (Enterprise)                                       │    │
│  │     └─► Google Workspace                                                │    │
│  │     └─► Microsoft Entra ID                                              │    │
│  │     └─► Okta, Auth0                                                     │    │
│  │                                                                         │    │
│  └─────────────────────────────────────────────────────────────────────────┘    │
│                                                                                  │
│  AUTHORIZATION                                                                   │
│  ═════════════                                                                   │
│                                                                                  │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │                                                                         │    │
│  │  RBAC (Role-Based Access Control)                                       │    │
│  │  ───────────────────────────────                                        │    │
│  │                                                                         │    │
│  │  Roles:                                                                 │    │
│  │  • super_admin    - Full system access (cross-tenant)                  │    │
│  │  • admin          - Full tenant access                                  │    │
│  │  • hr_manager     - HR module full access                              │    │
│  │  • hr_user        - HR module limited access                           │    │
│  │  • asset_manager  - Asset module full access                           │    │
│  │  • asset_user     - Asset module limited access                        │    │
│  │  • lms_admin      - LMS module full access                             │    │
│  │  • learner        - Course access only                                  │    │
│  │  • shop_admin     - Shop module full access                            │    │
│  │  • customer       - Order/view products only                           │    │
│  │                                                                         │    │
│  │  Permissions (Examples):                                                │    │
│  │  • employees.view, employees.create, employees.update, employees.delete │    │
│  │  • assets.view, assets.scan, assets.transfer, assets.delete            │    │
│  │  • courses.view, courses.manage, courses.publish                       │    │
│  │  • orders.view, orders.manage, orders.refund                           │    │
│  │                                                                         │    │
│  └─────────────────────────────────────────────────────────────────────────┘    │
│                                                                                  │
│  DATA PROTECTION                                                                 │
│  ═══════════════                                                                 │
│                                                                                  │
│  ┌─────────────────────────────────────────────────────────────────────────┐    │
│  │                                                                         │    │
│  │  Encryption at Rest:                                                    │    │
│  │  • Database: TDE (Transparent Data Encryption) - optional              │    │
│  │  • Files: AES-256 for sensitive documents                              │    │
│  │  • Secrets: Laravel encrypted environment                              │    │
│  │                                                                         │    │
│  │  Encryption in Transit:                                                 │    │
│  │  • TLS 1.3 enforced                                                     │    │
│  │  • HSTS headers                                                         │    │
│  │  • Certificate pinning (mobile)                                         │    │
│  │                                                                         │    │
│  │  Sensitive Fields (Auto-encrypted):                                     │    │
│  │  • employee.salary                                                      │    │
│  │  • employee.ssn                                                         │    │
│  │  • user.two_factor_secret                                              │    │
│  │                                                                         │    │
│  └─────────────────────────────────────────────────────────────────────────┘    │
│                                                                                  │
│  SECURITY HEADERS                                                                │
│  ════════════════                                                                │
│                                                                                  │
│  Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'  │
│  X-Frame-Options: DENY                                                           │
│  X-Content-Type-Options: nosniff                                                │
│  X-XSS-Protection: 1; mode=block                                                │
│  Referrer-Policy: strict-origin-when-cross-origin                               │
│  Permissions-Policy: geolocation=(), microphone=(), camera=()                   │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 11. Verzeichnisstruktur

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          PROJECT STRUCTURE                                       │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  openbiz-suite/                                                                  │
│  │                                                                               │
│  ├── docker/                           # Docker configuration                   │
│  │   ├── nginx/                                                                  │
│  │   │   └── conf.d/                                                            │
│  │   │       └── app.conf                                                       │
│  │   ├── php/                                                                    │
│  │   │   ├── Dockerfile                                                         │
│  │   │   ├── php.ini                                                            │
│  │   │   └── www.conf                                                           │
│  │   ├── mysql/                                                                  │
│  │   │   └── init/                     # Initial SQL scripts                    │
│  │   └── traefik/                                                               │
│  │       └── certs/                    # SSL certificates                       │
│  │                                                                               │
│  ├── src/                              # Laravel Application                    │
│  │   ├── app/                                                                    │
│  │   │   ├── Actions/                  # Single-purpose action classes         │
│  │   │   │   ├── HR/                                                            │
│  │   │   │   │   ├── CreateEmployeeAction.php                                  │
│  │   │   │   │   ├── ClockInAction.php                                         │
│  │   │   │   │   └── ApproveLeaveAction.php                                    │
│  │   │   │   ├── Asset/                                                         │
│  │   │   │   ├── LMS/                                                           │
│  │   │   │   └── Shop/                                                          │
│  │   │   │                                                                       │
│  │   │   ├── Console/                                                           │
│  │   │   │   └── Commands/                                                      │
│  │   │   │       ├── Tenant/                                                    │
│  │   │   │       └── Workflow/                                                  │
│  │   │   │                                                                       │
│  │   │   ├── Events/                   # Domain events                          │
│  │   │   │   ├── Employee/                                                      │
│  │   │   │   │   ├── EmployeeCreated.php                                       │
│  │   │   │   │   └── EmployeeUpdated.php                                       │
│  │   │   │   ├── Asset/                                                         │
│  │   │   │   └── Order/                                                         │
│  │   │   │                                                                       │
│  │   │   ├── Filament/                 # Admin Panel (Filament)                │
│  │   │   │   ├── Resources/                                                     │
│  │   │   │   │   ├── EmployeeResource.php                                      │
│  │   │   │   │   ├── AssetResource.php                                         │
│  │   │   │   │   └── CourseResource.php                                        │
│  │   │   │   ├── Pages/                                                         │
│  │   │   │   └── Widgets/                                                       │
│  │   │   │                                                                       │
│  │   │   ├── Http/                                                              │
│  │   │   │   ├── Controllers/                                                   │
│  │   │   │   │   ├── Api/              # REST API Controllers                  │
│  │   │   │   │   │   └── V1/                                                   │
│  │   │   │   │   │       ├── EmployeeController.php                            │
│  │   │   │   │   │       └── AssetController.php                               │
│  │   │   │   │   └── Web/              # Web Controllers                       │
│  │   │   │   ├── Middleware/                                                    │
│  │   │   │   │   ├── TenantMiddleware.php                                      │
│  │   │   │   │   └── RateLimitMiddleware.php                                   │
│  │   │   │   └── Requests/             # Form Requests                         │
│  │   │   │                                                                       │
│  │   │   ├── Jobs/                     # Queue Jobs                            │
│  │   │   │   ├── ProcessWorkflowJob.php                                        │
│  │   │   │   ├── GenerateCertificateJob.php                                    │
│  │   │   │   └── SyncLexwareJob.php                                            │
│  │   │   │                                                                       │
│  │   │   ├── Listeners/                # Event Listeners                       │
│  │   │   │                                                                       │
│  │   │   ├── Livewire/                 # Livewire Components                   │
│  │   │   │   ├── HR/                                                            │
│  │   │   │   │   ├── EmployeeTable.php                                         │
│  │   │   │   │   ├── TimeTracker.php                                           │
│  │   │   │   │   └── LeaveCalendar.php                                         │
│  │   │   │   ├── Asset/                                                         │
│  │   │   │   │   ├── AssetScanner.php                                          │
│  │   │   │   │   └── AssetTransfer.php                                         │
│  │   │   │   ├── LMS/                                                           │
│  │   │   │   └── Workflow/                                                      │
│  │   │   │       └── WorkflowBuilder.php                                       │
│  │   │   │                                                                       │
│  │   │   ├── Models/                   # Eloquent Models                       │
│  │   │   │   ├── Traits/                                                        │
│  │   │   │   │   ├── BelongsToTenant.php                                       │
│  │   │   │   │   └── HasAuditLog.php                                           │
│  │   │   │   ├── Tenant.php                                                     │
│  │   │   │   ├── User.php                                                       │
│  │   │   │   ├── Employee.php                                                   │
│  │   │   │   ├── Asset.php                                                      │
│  │   │   │   ├── Course.php                                                     │
│  │   │   │   └── Order.php                                                      │
│  │   │   │                                                                       │
│  │   │   ├── Notifications/            # Notification Classes                  │
│  │   │   │                                                                       │
│  │   │   ├── Observers/                # Model Observers                       │
│  │   │   │                                                                       │
│  │   │   ├── Policies/                 # Authorization Policies                │
│  │   │   │   ├── EmployeePolicy.php                                            │
│  │   │   │   └── AssetPolicy.php                                               │
│  │   │   │                                                                       │
│  │   │   ├── Providers/                                                         │
│  │   │   │   ├── AppServiceProvider.php                                        │
│  │   │   │   ├── EventServiceProvider.php                                      │
│  │   │   │   └── Filament/                                                      │
│  │   │   │                                                                       │
│  │   │   └── Services/                 # Business Logic Services               │
│  │   │       ├── AI/                                                            │
│  │   │       │   ├── AIService.php                                             │
│  │   │       │   ├── Drivers/                                                   │
│  │   │       │   │   ├── ClaudeDriver.php                                      │
│  │   │       │   │   └── OpenAIDriver.php                                      │
│  │   │       ├── QRCode/                                                        │
│  │   │       │   └── QRCodeService.php                                         │
│  │   │       ├── PDF/                                                           │
│  │   │       │   └── PDFService.php                                            │
│  │   │       ├── Workflow/                                                      │
│  │   │       │   ├── WorkflowEngine.php                                        │
│  │   │       │   ├── Actions/                                                   │
│  │   │       │   └── Triggers/                                                  │
│  │   │       └── Integration/                                                   │
│  │   │           └── LexwareService.php                                        │
│  │   │                                                                           │
│  │   ├── config/                       # Configuration files                   │
│  │   │   ├── openbiz.php               # Main app config                       │
│  │   │   ├── modules.php               # Module configuration                  │
│  │   │   └── ai.php                    # AI service config                     │
│  │   │                                                                           │
│  │   ├── database/                                                              │
│  │   │   ├── factories/                                                         │
│  │   │   ├── migrations/                                                        │
│  │   │   └── seeders/                                                           │
│  │   │                                                                           │
│  │   ├── graphql/                      # GraphQL Schema                        │
│  │   │   ├── schema.graphql                                                     │
│  │   │   └── types/                                                             │
│  │   │                                                                           │
│  │   ├── resources/                                                             │
│  │   │   ├── css/                                                               │
│  │   │   │   └── app.css                                                       │
│  │   │   ├── js/                                                                │
│  │   │   │   └── app.js                                                        │
│  │   │   └── views/                                                             │
│  │   │       ├── layouts/                                                       │
│  │   │       │   └── app.blade.php                                             │
│  │   │       ├── livewire/                                                      │
│  │   │       ├── components/                                                    │
│  │   │       └── pdf/                  # PDF templates                         │
│  │   │           ├── certificate.blade.php                                     │
│  │   │           ├── invoice.blade.php                                         │
│  │   │           └── asset-label.blade.php                                     │
│  │   │                                                                           │
│  │   ├── routes/                                                                │
│  │   │   ├── api.php                                                            │
│  │   │   ├── web.php                                                            │
│  │   │   └── channels.php                                                       │
│  │   │                                                                           │
│  │   ├── storage/                                                               │
│  │   │                                                                           │
│  │   └── tests/                                                                 │
│  │       ├── Feature/                                                           │
│  │       ├── Unit/                                                              │
│  │       └── Pest.php                                                           │
│  │                                                                               │
│  ├── docs/                             # Documentation                          │
│  │   ├── api/                                                                    │
│  │   │   └── openapi.yaml                                                       │
│  │   ├── architecture/                                                          │
│  │   └── deployment/                                                            │
│  │                                                                               │
│  ├── .env.example                                                               │
│  ├── docker-compose.yml                                                         │
│  ├── docker-compose.dev.yml                                                     │
│  ├── docker-compose.prod.yml                                                    │
│  ├── Makefile                          # Development commands                  │
│  └── README.md                                                                   │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 12. Deployment

### 12.1 Development Setup

```bash
# Clone repository
git clone https://github.com/yourusername/openbiz-suite.git
cd openbiz-suite

# Copy environment file
cp .env.example .env

# Start with development profile (includes phpMyAdmin, Mailhog)
docker compose --profile dev up --build

# Run migrations and seed
docker compose exec app php artisan migrate --seed

# Generate application key
docker compose exec app php artisan key:generate

# Access at:
# - App:        http://localhost
# - phpMyAdmin: http://localhost:8081
# - Mailhog:    http://localhost:8025
# - MinIO:      http://localhost:9001
# - Traefik:    http://localhost:8080
```

### 12.2 Production Deployment

```yaml
# docker-compose.prod.yml
version: '3.8'

services:
  app:
    image: ghcr.io/yourusername/openbiz-suite:latest
    environment:
      APP_ENV: production
      APP_DEBUG: "false"
    deploy:
      replicas: 3
      resources:
        limits:
          cpus: '2'
          memory: 2G

  nginx:
    deploy:
      replicas: 2

  worker:
    deploy:
      replicas: 2

  mysql:
    volumes:
      - mysql_data:/var/lib/mysql
    deploy:
      placement:
        constraints:
          - node.role == manager
```

---

## 📋 Checkliste für Entwicklung

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                         DEVELOPMENT CHECKLIST                                    │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│  PHASE 1: Foundation (Week 1-2)                                                 │
│  ═══════════════════════════════                                                │
│  [ ] Docker setup with all containers                                           │
│  [ ] Laravel installation with packages                                         │
│  [ ] Multi-tenancy implementation                                               │
│  [ ] Authentication (Sanctum + 2FA)                                            │
│  [ ] RBAC (Spatie Permissions)                                                  │
│  [ ] Audit logging                                                              │
│  [ ] Base Filament admin setup                                                  │
│                                                                                  │
│  PHASE 2: HR Module (Week 3-4)                                                  │
│  ════════════════════════════                                                   │
│  [ ] Employee CRUD                                                              │
│  [ ] Department & Position management                                           │
│  [ ] Time tracking (clock in/out)                                              │
│  [ ] Leave management                                                           │
│  [ ] Document upload                                                            │
│  [ ] HR Dashboard                                                               │
│                                                                                  │
│  PHASE 3: Asset Module (Week 5-6)                                               │
│  ═════════════════════════════════                                              │
│  [ ] Asset CRUD                                                                 │
│  [ ] Category & Location management                                             │
│  [ ] QR code generation                                                         │
│  [ ] Mobile scanner (PWA)                                                       │
│  [ ] Transfer workflow                                                          │
│  [ ] Maintenance scheduling                                                     │
│  [ ] Label PDF generation                                                       │
│                                                                                  │
│  PHASE 4: API Gateway (Week 7-8)                                                │
│  ═══════════════════════════════                                                │
│  [ ] REST API implementation                                                    │
│  [ ] GraphQL setup (Lighthouse)                                                 │
│  [ ] Webhook system                                                             │
│  [ ] API documentation (OpenAPI)                                                │
│  [ ] Rate limiting                                                              │
│  [ ] API key management                                                         │
│                                                                                  │
│  PHASE 5: LMS Module (Week 9-10)                                                │
│  ═══════════════════════════════                                                │
│  [ ] Course & Lesson CRUD                                                       │
│  [ ] Quiz system                                                                │
│  [ ] Enrollment management                                                      │
│  [ ] Progress tracking                                                          │
│  [ ] Certificate generation                                                     │
│  [ ] Gamification (badges)                                                      │
│                                                                                  │
│  PHASE 6: Advanced Features (Week 11-12)                                        │
│  ════════════════════════════════════════                                       │
│  [ ] Workflow Engine                                                            │
│  [ ] AI Integration                                                             │
│  [ ] Shop Module (basic)                                                        │
│  [ ] Lexware Office integration                                                 │
│                                                                                  │
│  PHASE 7: Polish (Week 13-14)                                                   │
│  ════════════════════════════                                                   │
│  [ ] Testing (>80% coverage)                                                    │
│  [ ] Documentation                                                              │
│  [ ] Demo data & videos                                                         │
│  [ ] Performance optimization                                                   │
│  [ ] Security audit                                                             │
│  [ ] README & Portfolio                                                         │
│                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🎯 Erfolgs-Metriken

Um dieses Projekt als Portfolio-Stück zu nutzen, sollte es folgende Kriterien erfüllen:

1. **One-Command Setup**: `docker compose up --build` startet alles
2. **Demo-Ready**: Seed data + Demo-Account für sofortiges Testing
3. **Dokumentiert**: README mit Screenshots, API-Docs, Architecture
4. **Tested**: Minimum 80% Test-Coverage
5. **Modern**: Aktueller Tech-Stack (Laravel 11, PHP 8.3, etc.)
6. **Unique**: Features die Konkurrenten nicht haben (Workflow Engine, AI)

---

