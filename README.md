# OpenBiz Suite

A modular, self-hostable business platform for SMEs.
HR, Assets, LMS, and more - all in one place.

---

## Architecture

<img width="1360" height="986" alt="grafik" src="https://github.com/user-attachments/assets/df632900-9d09-4b29-a87a-60d894b95089" />


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
```

The application container will automatically:
- Install Composer dependencies
- Generate application key
- Run database migrations
- Seed the database (if empty)

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
