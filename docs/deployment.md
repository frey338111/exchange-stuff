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
docker compose -f compose.prod.yaml exec app php artisan db:seed --class=CategorySeeder --force
docker compose -f compose.prod.yaml exec app php artisan db:seed --class=ProductConditionSeeder --force
```

Create the public storage symlink:

```bash
docker compose -f compose.prod.yaml exec app php artisan storage:link
```

Do not run the full `db:seed --force` command against the default production image. `DatabaseSeeder` creates demo users/listings through factories and Faker, which are development-only dependencies.

## Demo/Test Seed Data

If this EC2 environment should include all seeders and demo/test data, build the image with development dependencies enabled:

```bash
INSTALL_DEV_DEPENDENCIES=true docker compose -f compose.prod.yaml up -d --build
docker compose -f compose.prod.yaml exec app php artisan migrate:fresh --seed --force
```

This resets the database. If you already have data you want to keep, use `db:seed --force` instead of `migrate:fresh --seed --force`.

## Updates

Pull the latest code, rebuild the image, and restart the services:

```bash
./scripts/deploy-production.sh
```

The app image builds Composer dependencies and Vite assets during `docker compose -f compose.prod.yaml build`; you do not need Node.js, Composer, `vendor`, or `node_modules` on the EC2 host.

## Automatic Deployment

The `.github/workflows/deploy-production.yml` workflow runs after every push to `master`. It connects to the EC2 instance over SSH and runs `./scripts/deploy-production.sh` inside the server clone.

Configure these GitHub repository secrets:

- `EC2_HOST`: public IP or hostname for the EC2 instance.
- `EC2_USER`: SSH user, for example `ubuntu`.
- `EC2_SSH_KEY`: private SSH key with access to the EC2 instance.
- `EC2_APP_DIR`: absolute path to this repository on EC2.
- `EC2_SSH_PORT`: optional SSH port. Use `22` if not customized.

The deploy script fetches `origin/master`, fast-forwards the server clone, rebuilds and restarts the production Compose stack, runs migrations, refreshes the storage symlink, and checks the app with `php artisan about`.

## Environment Changes

The production Compose file mounts the server `.env` file into the Laravel containers at `/var/www/html/.env`.

After deploying this Compose change for the first time, recreate the Laravel containers so Docker applies the new bind mount:

```bash
docker compose -f compose.prod.yaml up -d --force-recreate app queue scheduler nginx
```

After later edits to `.env` on the server, restart the Laravel services so the production config cache is rebuilt with the new values:

```bash
docker compose -f compose.prod.yaml restart app queue scheduler
```

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
