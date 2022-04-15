FROM ubuntu:21.10

ENV DEBIAN_FRONTEND=noninteractive \
    TZ=UTC

RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y vim curl git unzip
RUN apt-get install -y php8.0-cli php8.0-zip php8.0-curl php8.0-xml php8.0-mbstring php8.0-xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

WORKDIR /home/magephp
