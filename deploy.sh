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

# Функция аварийного выхода и очистки: гарантирует снятие заглушки и возврат прав
cleanup() {
    local exit_code=$?
    if [ $exit_code -ne 0 ]; then
        log "ОШИБКА: Деплой завершился со сбоем (код $exit_code). Выполняем аварийный откат..."
    fi

    # Снимаем заглушку Nginx, если она осталась включенной
    if [ -f "$NGINX_DIR/site_update.conf" ]; then
        log "Снимаем страницу заглушки Nginx..."
        mv "$NGINX_DIR/ssl_salvi.ua" "$NGINX_DIR/ssl_salvi.ua.conf" 2>/dev/null || true
        mv "$NGINX_DIR/site_update.conf" "$NGINX_DIR/site_update" 2>/dev/null || true
        systemctl restart nginx 2>/dev/null || true
    fi

    rm -f "$PROJECT_DIR/deploy"

    # Гарантированно восстанавливаем права salvi:salvi
    log "Восстанавливаем права доступа salvi:salvi..."
    chown -R salvi:salvi "$PROJECT_DIR" 2>/dev/null || true
    chmod -R 775 "$PROJECT_DIR/api/runtime" "$PROJECT_DIR/console/runtime" 2>/dev/null || true

    if [ $exit_code -eq 0 ]; then
        log "=== Деплой SALVI API успешно завершен! ==="
    else
        log "=== Деплой завершился с ошибкой! Сайт возвращен в рабочий режим. ==="
    fi
}
trap cleanup EXIT

cd "$PROJECT_DIR"

# 1. Включаем заглушку
log "1. Включаем страницу обновления (Maintenance Mode)..."
touch "$PROJECT_DIR/deploy"
if [ -f "$NGINX_DIR/ssl_salvi.ua.conf" ]; then
    mv "$NGINX_DIR/ssl_salvi.ua.conf" "$NGINX_DIR/ssl_salvi.ua"
    mv "$NGINX_DIR/site_update" "$NGINX_DIR/site_update.conf"
    systemctl restart nginx
fi

# 2. Создаем резервную копию
log "2. Создаем резервную копию проекта..."
mkdir -p "$BACKUP_DIR"
BACKUP_FILE="$BACKUP_DIR/salvi_api_$(date +%F_%H-%M-%S).tar.gz"
tar -cpzf "$BACKUP_FILE" \
    --exclude='*/api/web*' \
    --exclude='*/api/runtime*' \
    --exclude='*/console/runtime*' \
    --exclude='*/node_modules*' \
    "$PROJECT_DIR" 2>/dev/null || true
log "Бэкап сохранен: $BACKUP_FILE"

# 3. Подтягиваем свежий код из Git
log "3. Обновляем кодовую базу Git (ветка: $BRANCH)..."
git fetch --all
git reset --hard "origin/$BRANCH"
git pull origin "$BRANCH"

# Очистка мусорных файлов IDE
find . -type d \( -name '.idea' -o -name '.vscode' \) -exec rm -rf {} + 2>/dev/null || true

# 4. Обновляем зависимости Composer
log "4. Устанавливаем зависимости Composer..."
export COMPOSER_PROCESS_TIMEOUT=1800
export COMPOSER_HTTP_TIMEOUT=60
composer install --no-dev --optimize-autoloader --no-interaction

# 5. Применяем миграции БД
log "5. Применяем миграции базы данных Yii2..."
php yii migrate --interactive=0

# 6. Сбрасываем кэш и перезапускаем воркеры
log "6. Сбрасываем кэш и перезапускаем PHP-FPM..."
php yii cache/flush-all 2>/dev/null || true
systemctl reload php-fpm

# 7. Отключаем заглушку Nginx
log "7. Отключаем заглушку и возвращаем рабочий конфиг Nginx..."
if [ -f "$NGINX_DIR/site_update.conf" ]; then
    mv "$NGINX_DIR/ssl_salvi.ua" "$NGINX_DIR/ssl_salvi.ua.conf"
    mv "$NGINX_DIR/site_update.conf" "$NGINX_DIR/site_update"
    systemctl restart nginx
fi
rm -f "$PROJECT_DIR/deploy"
