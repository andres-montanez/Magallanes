FROM ubuntu:16.04

RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y vim curl git unzip
RUN apt-get install -y php7.0-cli php-zip php7.0-curl php7.0-xml

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

WORKDIR /home/magephp
