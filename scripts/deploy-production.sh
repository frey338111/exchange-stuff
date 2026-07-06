#!/usr/bin/env bash
set -Eeuo pipefail

APP_DIR="${APP_DIR:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
BRANCH="${DEPLOY_BRANCH:-master}"
COMPOSE_FILE="${COMPOSE_FILE:-compose.prod.yaml}"
LOCK_FILE="${LOCK_FILE:-/tmp/exchange-stuff-deploy.lock}"

log() {
    printf '[%s] %s\n' "$(date -u '+%Y-%m-%dT%H:%M:%SZ')" "$*"
}

cd "$APP_DIR"

exec 9>"$LOCK_FILE"
if ! flock -n 9; then
    log "Another deployment is already running."
    exit 1
fi

log "Starting deployment in $APP_DIR on branch $BRANCH"

if [ ! -f .env ]; then
    log "Missing .env file. Create it from .env.production.example before deploying."
    exit 1
fi

if ! git diff --quiet || ! git diff --cached --quiet; then
    log "Working tree has uncommitted changes. Refusing to deploy."
    git status --short
    exit 1
fi

git fetch origin "$BRANCH"
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"

docker compose -f "$COMPOSE_FILE" up -d --build --remove-orphans
docker compose -f "$COMPOSE_FILE" exec -T app php artisan migrate --force
docker compose -f "$COMPOSE_FILE" exec -T app php artisan storage:link || true
docker compose -f "$COMPOSE_FILE" exec -T app php artisan about

log "Deployment complete."
