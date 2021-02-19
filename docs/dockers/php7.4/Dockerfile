FROM ubuntu:20.04

ENV DEBIAN_FRONTEND=noninteractive \
    TZ=UTC

RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y vim curl git unzip
RUN apt-get install -y php7.4-cli php-zip php7.4-curl php7.4-xml php7.4-mbstring php7.4-xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

WORKDIR /home/magephp
