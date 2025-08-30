#!/bin/bash
set -euo pipefail

echo "ENTRYPOINT START: PROJECT_PATH='${PROJECT_PATH:-unset}', USER_DOCKER_ID='${USER_DOCKER_ID:-unset}', GROUP_DOCKER_ID='${GROUP_DOCKER_ID:-unset}'" >&2

if [ -z "${PROJECT_PATH:-}" ] || [ -z "${USER_DOCKER_ID:-}" ] || [ -z "${GROUP_DOCKER_ID:-}" ]; then
  echo "ERROR: Required environment variables are not set. Ensure they are passed via docker-compose environment or build-args." >&2
  exit 1
fi

APP_PATH="${PROJECT_PATH}"
STORAGE_DIR="$APP_PATH/storage"
LOGS_DIR="$STORAGE_DIR/logs"
CACHE_DIR="$STORAGE_DIR/framework/cache"
BOOTSTRAP_CACHE_DIR="$APP_PATH/bootstrap/cache"

ensure_dir() {
  if [ ! -d "$1" ]; then
    mkdir -p "$1"
  fi
  chown -R "$USER_DOCKER_ID:$GROUP_DOCKER_ID" "$1"
  chmod -R 775 "$1"
}

echo "Setting up Laravel storage/cache directories..."
ensure_dir "$LOGS_DIR"
ensure_dir "$CACHE_DIR"
ensure_dir "$BOOTSTRAP_CACHE_DIR"

if [ ! -d "$LOGS_DIR" ]; then
  mkdir -p "$LOGS_DIR"
  chown -R "$USER_DOCKER_ID:$GROUP_DOCKER_ID" "$LOGS_DIR"
  chmod -R 775 "$LOGS_DIR"
fi

if [ "$#" -gt 0 ]; then
  exec "$@"
else
  tail -f /dev/null
fi