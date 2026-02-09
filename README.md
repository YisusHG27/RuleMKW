Antes de intentar ejecutar a la aplicacion has de realizar varias cosas

## Tendrás que descargar docker desktop a traves de la página de docker
[Página de Docker Desktop](https://www.docker.com/products/docker-desktop/)

## 1 Has de importar las imagenes necesarias
lo harás a traves de estos comandos:

# Descargar Nginx
docker pull nginx:alpine

# Descargar PHP
docker pull php:8.1-fpm-alpine

# Descargar MySQL
docker pull mysql:8.0

# Descargar PHPMyAdmin
docker pull phpmyadmin/phpmyadmin

## luego ejecutamos docker compose

# Navegar a tu carpeta
cd "C:\Users\Jesús\Documents\GitHub\RuleMKW"

# Verificar que tienes docker-compose.yml
dir *.yml

# Iniciar todos los servicios
docker-compose up -d

# Verificar que están corriendo
docker-compose ps