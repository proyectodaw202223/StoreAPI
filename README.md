## Configurar Contenedor de Desarrollo

La pila multicontenedor de desarrollo consiste en un contenedor de mysql, uno de phpmyadmin y uno de ubuntu con laravel instalado que se contruye con un Dockerfile. Para montar la pila pon los siguientes archivos en un directorio y ejecuta `docker-compose up -d`. El proyecto Laravel se habrá creado en el subdirectorio `/StoreAPI`, es posible que tengas que darte permisos para ejecutar comandos de git para ello ejecuta `sudo chown --recursive <user>:<group> <path>` en linux.

### docker-compose.yml

**MySQL:** El servicio corre en el puerto 3307 del host pero se puede cambiar a otro puerto si lo tienes ocupado por otro servicio, acuerdate de configurar el puerto correcto en el fichero `.env` del proyecto laravel. El volumen `./volumes/initdb` se usa para ejecutar los scripts sql que contenga el directorio al crear el contenedor (de momento está vacio y no hace nada). El volumen `./volumes/mysql` se usa para conservar las bases de datos si eliminas el contenedor y creas uno nuevo.

**PhpMyAdmin:** PhpMyAdmin corre en el puerto 8100 del host, lo puedes cambiar si tienes el puerto ocupado no influye en nada. Para iniciar sesión hay que especificar Server: mysql Username: root Password: root.

**StoreAPI:** La API se contruye con el Dockerfile y usa la red del host esto quiere decir que cuando se ejecute `php artisan serve` (se ejecuta cada vez que se inicia el contenedor) el contenedor empezará a probar todos los puertos del host desde el 8000 hacia arriba y se ejecutar en el primero que no esté ocupado.

```yml
version: '3'
services:
  mysql:
    # https://hub.docker.com/_/mysql
    image: mysql:latest
    container_name: proyecto-daw-mysql
    restart: unless-stopped
    ports:
      - 3307:3306
    environment:
      MYSQL_ROOT_PASSWORD: root
    volumes:
      - ./volumes/initdb/:/docker-entrypoint-initdb.d
      - ./volumes/mysql:/var/lib/mysql

  phpmyadmin:
    # https://hub.docker.com/_/phpmyadmin
    image: phpmyadmin:latest
    container_name: proyecto-daw-phpmyadmin
    restart: unless-stopped
    ports:
      - 8100:80
    environment:
      PMA_ARBITRARY: 1
      MYSQL_ROOT_PASSWORD: root

  store-api:
    build: .
    container_name: proyecto-daw-store-api
    restart: unless-stopped
    volumes:
      - ./StoreAPI:/StoreAPI
    network_mode: host
    depends_on:
      - mysql

```

### Dockerfile

El Dockerfile crea una imagen a partir de la imagen de ubuntu:22.04 en la que se instala todo lo necesario para desarrollar con Laravel. Se usa git para bajar todos los cambios del repositorio de GitHub a un proyecto de Laravel nuevo, se hace de esta forma en luegar de clonando el repositorio porque hay archivos como el `.env` que no se encuentran en el repo, al crear un proyecto nuevo de Laravel se crean estos ficheros y deben ser configurados por el usuario del contedor.

```Dockerfile
FROM ubuntu:22.04

# Copy entrypoint script to /
COPY ./docker-entrypoint.sh /

RUN chmod +x /docker-entrypoint.sh

RUN apt update

# Install git, php and laravel dependencies
RUN DEBIAN_FRONTEND=noninteractive apt install -qq -y \
git php openssl php-common php-curl php-json php-mbstring php-mysql php-xml php-zip

# Install composer from official guide: https://getcomposer.org/download/
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

# Install laravel
RUN composer global require laravel/installer

# Add the directory /.composer/vendor/bin/ to the PATH on startup.
RUN echo "export PATH=~/.composer/vendor/bin/:$PATH" >> ~/.bashrc

# Create Laravel proyect
RUN composer create-project --prefer-dist laravel/laravel StoreAPI-bkp

WORKDIR /StoreAPI-bkp

# Delete laravel default migrations and Models
RUN rm -f database/migrations/*
RUN rm -f app/Models/*

# Init repository with latest commits from GitHub
RUN git init
RUN git remote add origin https://github.com/proyectodaw202223/StoreAPI
RUN git fetch --all
RUN git reset --hard origin/master

RUN mkdir ../StoreAPI
WORKDIR /StoreAPI

CMD ["/docker-entrypoint.sh"]
```

### docker-entrypoint.sh

Esto es un script de bash que está configurado en el Dockerfile para ejecutarse cada vez que se inicia el contenedor de desarrollo. El script copia el contenido de /StoreAPI-bkp (aquí es donde el Dockerfile baja el código fuente del repo de GitHub) a /StoreAPI si está vacio (este es el volumen compartido con el host para poder editar el código); despues ejecuta `php composer serve` para iniciar el servidor con el código fuente de la API.

```bash
#!/bin/bash

# Si el directorio /StoreAPI existe
if [ -d "/StoreAPI" ]; then
    # Si el directorio /StoreAPI est
    if [ -z "$(ls -A /StoreAPI)" ]; then
        cp -r /StoreAPI-bkp/. /StoreAPI;
    fi

    php artisan serve;
fi
```
