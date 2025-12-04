# OpenBiz Suite

A modular, self-hostable business platform for SMEs.
HR, Assets, LMS, and more - all in one place.

---

## Architecture

```
                              ┌─────────────────────────────────────────────────────────────┐
                              │                        FRONTEND NETWORK                     │
                              └─────────────────────────────────────────────────────────────┘
                                                          │
                                    ┌─────────────────────┴─────────────────────┐
                                    │                                           │
                              ┌─────┴─────┐                               ┌─────┴─────┐
        :80/:443 ────────────►│  TRAEFIK  │                               │   NGINX   │◄──────────── :8000
          :8080 (dashboard)   │  reverse  │                               │    web    │
                              │   proxy   │                               │  server   │
                              └───────────┘                               └─────┬─────┘
                                                                                │
                              ┌─────────────────────────────────────────────────┼─────────────┐
                              │                        BACKEND NETWORK          │             │
                              └─────────────────────────────────────────────────┼─────────────┘
                                                                                │
                    ┌───────────────────────────────────────────────────────────┼───────────────┐
                    │                                                           │               │
              ┌─────┴─────┐                                               ┌─────┴─────┐   ┌─────┴─────┐
              │  WORKER   │                                               │    APP    │   │ SCHEDULER │
              │  horizon  │                                               │  php 8.3  │   │   cron    │
              │  queues   │                                               │  laravel  │   │   tasks   │
              └─────┬─────┘                                               └─────┬─────┘   └─────┬─────┘
                    │                                                           │               │
                    └───────────────────────────────────────┬───────────────────┴───────────────┘
                                                            │
          ┌──────────────┬──────────────┬──────────────┬────┴─────────┬──────────────┐
          │              │              │              │              │              │
    ┌─────┴─────┐  ┌─────┴─────┐  ┌─────┴─────┐  ┌─────┴─────┐  ┌─────┴─────┐  ┌─────┴─────┐
    │   MYSQL   │  │   REDIS   │  │   MINIO   │  │  MEILI    │  │  SOKETI   │  │  MAILHOG  │
    │  database │  │   cache   │  │  storage  │  │  search   │  │ websocket │  │   mail    │
    │   :3306   │  │   :6379   │  │:9000/:9001│  │   :7700   │  │   :6001   │  │   :8025   │
    └───────────┘  └───────────┘  └───────────┘  └───────────┘  └───────────┘  └───────────┘
```

---

## What is it?

OpenBiz Suite is an open-source business platform with:

- **Multi-Tenant** - One installation, multiple companies
- **Modular** - Enable only the features you need
- **API-First** - REST & GraphQL ready
- **Offline-Capable** - Works as a PWA

**Modules:** Core (Auth, RBAC), HR, Assets, LMS, Workflow Engine, API Gateway

---

## Requirements

- **Docker** (with Docker Compose)
- **Make** *(optional, but recommended)*

---

## Quick Start

```bash
git clone https://github.com/yourusername/openbiz-suite.git
cd openbiz-suite
make install
```

Open http://localhost in your browser.

---

## Without Make

```bash
cp .env.example .env
docker compose build
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

---

## Useful Commands

| Command       | Description                    |
|---------------|--------------------------------|
| `make up`     | Start containers               |
| `make down`   | Stop containers                |
| `make logs`   | View logs                      |
| `make shell`  | Access container shell         |
| `make fresh`  | Reset database with seed data  |
| `make test`   | Run tests                      |

---

## Access Points

| Service           | URL                     |
|-------------------|-------------------------|
| App               | http://localhost        |
| phpMyAdmin        | http://localhost:8081   |
| Traefik Dashboard | http://localhost:8080   |
| MinIO Console     | http://localhost:9001   |

---

## License

Open-source software. Contributions welcome.
