# Tuition Management Backend (Laravel) — Docker Quick Start

A clean, dev-friendly Docker setup for a Laravel API with **PHP 8.2 FPM + Nginx + MySQL** and a **queue worker**. Broadcasting uses **Pusher** (hosted).

> **API Base URL (default):** `http://localhost:8080`

---

## Quick Start (Clone & Run)

> **You only need Docker & Git.** No local PHP/MySQL/Composer required.

```bash
# 1) Clone the repository
git clone <YOUR_REPO_URL>.git
cd <YOUR_REPO_DIR>

# 2) Prepare environment files
# If you have .env.example, copy it; otherwise ensure a .env exists with your secrets
cp .env.example .env  # (if .env.example exists)
# Create container-specific overrides
cp .env .env.docker   # then edit .env.docker as shown below

# 3) Build and start the stack
docker compose build
docker compose up -d

# 4) First-time Laravel setup (run inside containers)
docker compose exec app composer install
docker compose exec app php artisan key:generate   # skip if APP_KEY already set
docker compose exec app php artisan session:table
docker compose exec app php artisan cache:table
docker compose exec app php artisan queue:table
docker compose exec app php artisan migrate
docker compose exec app php artisan storage:link   # optional
docker compose exec app sh -lc "chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache"

# 5) Open the API
# http://localhost:8080
```

**MySQL (from host):** host `localhost`, port `3307`, user `root`, password from `.env.docker`, database `tuition_db`.

---

## Table of Contents

- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Project Structure](#project-structure)
- [Environment Setup](#environment-setup)
  - [.env](#env)
  - [.env.docker](#envdocker)
- [Start with Docker](#start-with-docker)
- [First-Time Laravel Setup](#first-time-laravel-setup)
- [Services & Ports](#services--ports)
- [Common Commands](#common-commands)
- [Queue Worker](#queue-worker)
- [Troubleshooting](#troubleshooting)
- [Using Host MySQL (Optional)](#using-host-mysql-optional)
- [Running Without Docker (Optional)](#running-without-docker-optional)

---

## Overview

- **Stack**: PHP 8.2 FPM, Nginx, MySQL 8, Composer
- **Queues/Sessions/Cache**: Database driver
- **Broadcasting**: Pusher (WebSockets via hosted service)
- **Goal**: Let anyone run the backend quickly without installing PHP/MySQL locally

---

## Prerequisites

- **Docker Desktop** (or Docker Engine)
- **Git** (to clone the repository)

> No local PHP/MySQL/Composer required.

---

## Project Structure

Minimal paths relevant to Docker setup:

```
.
├─ docker/
│  ├─ nginx/
│  │  └─ default.conf
│  └─ php/
│     └─ conf.d/
│        └─ custom.ini         # optional PHP overrides (can be any path/name on host)
├─ docker-compose.yml
├─ Dockerfile
├─ .env                        # Laravel environment
└─ (Laravel app files)
```

---

## Environment Setup

### .env

Your repository should include a `.env` (or provide a sample). Ensure broadcasting is set to **Pusher**:

```env
BROADCAST_CONNECTION=pusher
# If your app uses BROADCAST_DRIVER instead, set:
# BROADCAST_DRIVER=pusher
```

Also ensure your database connection is `mysql`.

> Do **not** commit secrets. Keep credentials in `.env` locally.

### .env.docker

Create a new file `.env.docker` for values that need to be different **inside containers**:

```env
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=tuition_db
DB_USERNAME=root
DB_PASSWORD=your_mysql_root_password

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

# Pusher (use your real keys here; not placeholders)
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=ap2
```

> Inside Docker, the **DB host** is the service name `mysql`, not `127.0.0.1`.

---

## Start with Docker

Build and start the stack:

```bash
docker compose build
docker compose up -d
```

Visit the API at **http://localhost:8080**.

---

## First-Time Laravel Setup

Run these **inside containers** (compose will route you to the right service):

```bash
# Install PHP deps
docker compose exec app composer install

# Generate app key (skip if APP_KEY already exists)
docker compose exec app php artisan key:generate

# Create tables for DB-backed drivers
docker compose exec app php artisan session:table
docker compose exec app php artisan cache:table
docker compose exec app php artisan queue:table

# Run migrations
docker compose exec app php artisan migrate

# (Optional) seed data
# docker compose exec app php artisan db:seed

# (Optional) storage symlink if you serve files
docker compose exec app php artisan storage:link

# Fix permissions if needed
docker compose exec app sh -lc "chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache"
```

---

## Services & Ports

| Service | Purpose         | Host Port → Container | Notes                           |
| ------: | --------------- | --------------------- | ------------------------------- |
|   nginx | HTTP entrypoint | `8080 → 80`           | Serves `/public` to the browser |
|     app | PHP-FPM runtime | n/a                   | Runs PHP 8.2 + Composer         |
|   mysql | Database        | `3307 → 3306`         | Use `localhost:3307` from host  |
|   queue | Queue worker    | n/a                   | Runs `php artisan queue:work`   |

> Change ports in `docker-compose.yml` if they clash on your machine.

---

## Common Commands

```bash
# Start / stop
docker compose up -d
docker compose down

# Rebuild after Dockerfile changes
docker compose build --no-cache

# Artisan / Composer / Shell
docker compose exec app php artisan <command>
docker compose exec app composer <command>
docker compose exec app sh

# Logs
docker compose logs -f nginx
docker compose logs -f app
docker compose logs -f queue

# MySQL from host (Workbench/TablePlus):
# host: localhost  port: 3307  user: root  pass: (from .env.docker)  db: tuition_db
```

---

## Queue Worker

A dedicated service runs the queue worker:

```bash
docker compose logs -f queue       # tail worker logs
docker compose restart queue       # restart worker
```

If you use Laravel Horizon later, replace the queue command with `php artisan horizon`.

---

## Troubleshooting

- **SQLSTATE[HY000] [2002] Connection refused**  
  Ensure `.env.docker` has `DB_HOST=mysql`. Restart containers after changes.

- **Port in use**  
  Edit `docker-compose.yml` to use different host ports, e.g.:
  ```yaml
  nginx:
    ports:
      - "8081:80"
  mysql:
    ports:
      - "3308:3306"
  ```

- **Permissions (storage/cache)**  
  Run the permission fix command shown in _First-Time Laravel Setup_.

- **Broadcasting still logs instead of Pusher**  
  Make sure `BROADCAST_CONNECTION=pusher` (or `BROADCAST_DRIVER=pusher` depending on your config). Clear caches:
  ```bash
  docker compose exec app php artisan config:clear
  docker compose exec app php artisan cache:clear
  ```

---

## Using Host MySQL (Optional)

If you already have MySQL installed and running locally, you can delete the `mysql` service from `docker-compose.yml` and set:

```env
DB_HOST=host.docker.internal
DB_PORT=3306
```

---

## Running Without Docker (Optional)

If you prefer running locally:

1. Install PHP 8.2, Composer, MySQL 8.
2. Set `.env` to use your local DB:
   ```env
   DB_HOST=127.0.0.1
   DB_PORT=3306
   ```
3. Run:
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate
   php artisan serve   # http://127.0.0.1:8000
   php artisan queue:work
   ```
