Antes de intentar ejecutar a la aplicacion has de realizar varias cosas

## Tendrás que descargar docker desktop a traves de la página de docker
[Página de Docker Desktop](https://www.docker.com/products/docker-desktop/)

## 1º Has de importar las imagenes necesarias
lo harás a traves de estos comandos:

# Descargas Nginx
docker pull nginx:alpine

# Descargas PHP
docker pull php:8.1-fpm-alpine

# Descargas MySQL
docker pull mysql:8.0

# Descargas PHPMyAdmin
docker pull phpmyadmin/phpmyadmin

## luego ejecutamos docker compose

# Verificas que tienes docker-compose.yml
dir *.yml

# Inicias todos los servicios
docker-compose up -d

# Verificas que están corriendo
docker-compose ps