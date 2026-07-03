# Deployment

This project keeps the local Sail `compose.yaml` unchanged and ships a production-oriented `compose.prod.yaml` for a single EC2 host.

## Services

- `nginx`: public HTTP entrypoint on `APP_PORT`, serving static files and forwarding PHP requests to PHP-FPM.
- `app`: Laravel PHP-FPM application container.
- `queue`: Laravel queue worker for queued mail and background jobs.
- `scheduler`: Laravel scheduler process.
- `mysql`: MySQL 8.4 with a persistent Docker volume.
- `redis`: Redis with append-only persistence for cache support.

## First Deploy

On the EC2 server, install Docker and the Docker Compose plugin, then clone the repository.

Create the production environment file:

```bash
cp .env.production.example .env
```

Edit `.env` and set:

- `APP_URL`
- `APP_KEY`
- `DB_PASSWORD`
- `DB_ROOT_PASSWORD`
- mail settings

Generate an application key if you do not already have one:

```bash
docker compose -f compose.prod.yaml run --rm app php artisan key:generate --show
```

Copy the printed key into `.env` as `APP_KEY`.

Build and start the stack:

```bash
docker compose -f compose.prod.yaml up -d --build
```

Run database migrations and seed required lookup data:

```bash
docker compose -f compose.prod.yaml exec app php artisan migrate --force
docker compose -f compose.prod.yaml exec app php artisan db:seed --force
```

Create the public storage symlink:

```bash
docker compose -f compose.prod.yaml exec app php artisan storage:link
```

## Updates

Pull the latest code, rebuild the image, and restart the services:

```bash
git pull
docker compose -f compose.prod.yaml up -d --build
docker compose -f compose.prod.yaml exec app php artisan migrate --force
```

The app image builds Composer dependencies and Vite assets during `docker compose -f compose.prod.yaml build`; you do not need Node.js, Composer, `vendor`, or `node_modules` on the EC2 host.

## Useful Commands

View logs:

```bash
docker compose -f compose.prod.yaml logs -f nginx app queue scheduler
```

Restart the queue worker after code or environment changes:

```bash
docker compose -f compose.prod.yaml restart queue
```

Run an Artisan command:

```bash
docker compose -f compose.prod.yaml exec app php artisan about
```

Open a shell inside the app container:

```bash
docker compose -f compose.prod.yaml exec app sh
```

## HTTPS

This Compose file exposes HTTP on port 80. Put HTTPS in front of it with one of these approaches:

- AWS Application Load Balancer with an ACM certificate.
- A host-level reverse proxy such as Caddy, Traefik, or Nginx with Certbot.

When HTTPS is enabled, keep `APP_URL=https://...` and `SESSION_SECURE_COOKIE=true`.

## Backups

The database lives in the `mysql-data` Docker volume. Back it up before deployments that change schema or data:

```bash
docker compose -f compose.prod.yaml exec mysql sh -c 'mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' > backup.sql
```
