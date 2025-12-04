# OpenBiz Suite

**A modular, API-first business platform for SMEs**

OpenBiz Suite is a self-hostable, open-source business platform that provides comprehensive solutions for HR management, asset tracking, learning management (LMS), and more - all in one unified system.

## Features

- **Multi-Tenant Architecture** - One installation, multiple companies
- **API-First Design** - Full REST & GraphQL API access
- **Modular System** - Enable only the features you need
- **Modern Tech Stack** - Laravel 11, PHP 8.3, Livewire 3, Tailwind CSS
- **Docker-Ready** - One command to start everything
- **Offline-Capable** - PWA with service workers

## Modules

- **Core** - Multi-tenancy, authentication, RBAC, audit logging
- **HR** - Employee management, time tracking, leave management
- **Assets** - Asset tracking with QR codes, maintenance scheduling
- **LMS** - Learning management with courses, quizzes, certificates
- **Workflow Engine** - Automate business processes
- **API Gateway** - REST API, GraphQL, webhooks

## Quick Start

### Prerequisites

- Docker & Docker Compose
- Git

### Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/openbiz-suite.git
cd openbiz-suite
```

2. Run the installation:
```bash
make install
```

That's it! The application will be available at:
- **App**: http://localhost
- **phpMyAdmin**: http://localhost:8081
- **Traefik Dashboard**: http://localhost:8080
- **MinIO Console**: http://localhost:9001

### Manual Installation

If you prefer manual steps:

```bash
# Copy environment file
cp .env.example .env

# Build containers
docker compose build

# Start services
docker compose up -d

# Install dependencies
docker compose exec app composer install

# Generate application key
docker compose exec app php artisan key:generate

# Run migrations and seed
docker compose exec app php artisan migrate --seed
```

## Development

### Useful Commands

```bash
# Start all services
make up

# Start with development tools (phpMyAdmin, Mailhog)
make up-dev

# View logs
make logs

# Access container shell
make shell

# Run migrations
make migrate

# Fresh database with seed data
make fresh

# Run tests
make test
```

### Project Structure

```
openbiz-suite/
├── docker/               # Docker configuration
│   ├── nginx/           # Nginx config
│   ├── php/             # PHP Dockerfile & config
│   ├── mysql/           # MySQL initialization
│   └── traefik/         # Reverse proxy config
├── src/                 # Laravel application
└── docker-compose.yml   # Main Docker Compose file
```

## Technology Stack

### Backend
- Laravel 11.x (PHP 8.3)
- MySQL 8.0
- Redis (Cache & Queue)
- Laravel Horizon (Queue Management)
- Laravel Scout + Meilisearch (Full-text Search)
- MinIO (S3-compatible Storage)

### Frontend
- Livewire 3 + Alpine.js
- Tailwind CSS 3
- Filament 3 (Admin Panel)
- Chart.js (Analytics)

### DevOps
- Docker & Docker Compose
- Nginx (Web Server)
- Traefik (Reverse Proxy)
- Soketi (WebSocket Server)

## License

This project is open-source software.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
