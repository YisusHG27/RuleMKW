#!/bin/sh
set -e

# Verificar si vendor existe, si no, ejecutar composer install
if [ ! -d "/var/www/html/src/vendor" ]; then
    echo "📦 Instalando dependencias con Composer..."
    cd /var/www/html
    if [ -f "composer.json" ]; then
        composer install --no-interaction
        echo "✅ Dependencias instaladas correctamente"
    else
        echo "⚠️ No se encontró composer.json"
    fi
else
    echo "✅ Vendor ya existe, saltando instalación"
fi

# Asegurar permisos de logs
chmod -R 777 /var/www/html/logs 2>/dev/null || true

# Ejecutar el comando original
exec docker-php-entrypoint "$@"