#!/usr/bin/env bash
# ==============================================================================
# SALVI API - Production Deployment Script
# ==============================================================================
set -e

PROJECT_DIR="/var/www/salvi/www_api"
NGINX_DIR="/etc/nginx/conf.d"
BACKUP_DIR="/var/www/salvi/auto-deployment-backup"
BRANCH="${DEPLOY_BRANCH:-main}"

log() {
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $1"
}

log "=== Начинаем деплой SALVI API (Бэкенд) ==="

# Функция аварийного выхода и очистки: гарантирует возврат прав и сброс состояния
cleanup() {
    local exit_code=$?
    if [ $exit_code -ne 0 ]; then
        log "ОШИБКА: Деплой завершился со сбоем (код $exit_code)."
    fi

    # Аварийная страховка: если на сервере вдруг осталась старая заглушка Nginx
    if [ -f "$NGINX_DIR/site_update.conf" ]; then
        log "Обнаружена активная заглушка Nginx, снимаем..."
        mv "$NGINX_DIR/ssl_salvi.ua" "$NGINX_DIR/ssl_salvi.ua.conf" 2>/dev/null || true
        mv "$NGINX_DIR/site_update.conf" "$NGINX_DIR/site_update" 2>/dev/null || true
        systemctl restart nginx 2>/dev/null || true
    fi
    rm -f "$PROJECT_DIR/deploy" 2>/dev/null || true

    # Гарантированно восстанавливаем права salvi:salvi
    log "Восстанавливаем права доступа salvi:salvi..."
    chown -R salvi:salvi "$PROJECT_DIR" 2>/dev/null || true
    chmod -R 775 "$PROJECT_DIR/api/runtime" "$PROJECT_DIR/console/runtime" 2>/dev/null || true

    if [ $exit_code -eq 0 ]; then
        log "=== Деплой SALVI API успешно завершен! ==="
    else
        log "=== Деплой завершился с ошибкой! ==="
    fi
}
trap cleanup EXIT

cd "$PROJECT_DIR"

# 1. Создаем резервную копию
log "1. Создаем резервную копию проекта..."
mkdir -p "$BACKUP_DIR"
BACKUP_FILE="$BACKUP_DIR/salvi_api_$(date +%F_%H-%M-%S).tar.gz"

# Используем многопоточный pigz (2 ядра), если установлен, иначе обычный gzip
if command -v pigz >/dev/null 2>&1; then
    COMPRESSOR="pigz -p 2"
    tar -cpf - \
        --exclude='*/api/web*' \
        --exclude='*/api/runtime*' \
        --exclude='*/console/runtime*' \
        --exclude='*/node_modules*' \
        --exclude='*/vendor*' \
        --exclude='*/nodejs*' \
        --exclude='*/.git*' \
        --exclude='*/console/xml/orders*' \
        --exclude='*/console/xml/1c_b2b.xml' \
        "$PROJECT_DIR" 2>/dev/null | pigz -p 2 > "$BACKUP_FILE"
else
    COMPRESSOR="gzip"
    tar -cpf - \
        --exclude='*/api/web*' \
        --exclude='*/api/runtime*' \
        --exclude='*/console/runtime*' \
        --exclude='*/node_modules*' \
        --exclude='*/vendor*' \
        --exclude='*/nodejs*' \
        --exclude='*/.git*' \
        --exclude='*/console/xml/orders*' \
        --exclude='*/console/xml/1c_b2b.xml' \
        "$PROJECT_DIR" 2>/dev/null | gzip > "$BACKUP_FILE"
fi

BACKUP_SIZE=$(du -h "$BACKUP_FILE" 2>/dev/null | cut -f1)
log "Бэкап сохранен ($COMPRESSOR, размер: ${BACKUP_SIZE:-0}): $BACKUP_FILE"

# Удаляем старые бэкапы бэкенда, оставляя 2 последних
if [ -s "$BACKUP_FILE" ]; then
    log "Удаляем старые бэкапы salvi_api, оставляя 2 последних..."
    ls -t "$BACKUP_DIR"/salvi_api_*.tar.gz 2>/dev/null | tail -n +3 | xargs rm -f 2>/dev/null || true
fi

# 2. Подтягиваем свежий код из Git
log "2. Обновляем кодовую базу Git (ветка: $BRANCH)..."
git fetch --all
git reset --hard "origin/$BRANCH"
git pull origin "$BRANCH"

# Очистка мусорных файлов IDE
find . -type d \( -name '.idea' -o -name '.vscode' \) -exec rm -rf {} + 2>/dev/null || true

# 3. Обновляем зависимости Composer
log "3. Устанавливаем зависимости Composer..."
export COMPOSER_PROCESS_TIMEOUT=1800
export COMPOSER_HTTP_TIMEOUT=60
composer install --optimize-autoloader --no-interaction

# 4. Применяем миграции БД
log "4. Применяем миграции базы данных Yii2..."
php yii migrate --interactive=0

# 5. Сбрасываем кэш и перезапускаем воркеры
log "5. Сбрасываем кэш и перезапускаем PHP-FPM..."
php yii cache/flush-all 2>/dev/null || true
systemctl reload php-fpm
