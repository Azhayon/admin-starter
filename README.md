# admin-starter

A reusable admin dashboard starter kit: a Laravel REST API (Sanctum SPA auth)
and a React SPA (Vite, Tailwind, shadcn/ui). Fork it as the base for new projects.

## Stack
- **api/** Laravel, Breeze (API stack), Sanctum, MySQL
- **web/** React (Vite), Tailwind CSS, shadcn/ui, axios, react-router-dom, TanStack Query
- **docs/** architecture decision records, API notes

## Structure
- `api/` Laravel REST API, versioned under `/api/v1`
- `web/src/features/` one folder per domain; `web/src/components/` generic, entity-agnostic building blocks
- `docs/adr/` architecture decision records

## Prerequisites
PHP, Composer, MySQL, Node 20.19+ (or 22.12+)

## Run it
**API** (http://localhost:8000)
```bash
cd api
cp .env.example .env      # then set DB credentials
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

**Web** (http://localhost:5173)
```bash
cd web
cp .env.example .env
npm install
npm run dev
```

Use `localhost` (not `127.0.0.1`) on both sides so session cookies work.

## Optional: MySQL via Docker
```bash
docker compose up -d   # then DB_PORT=3307, DB_PASSWORD=secret in api/.env
```