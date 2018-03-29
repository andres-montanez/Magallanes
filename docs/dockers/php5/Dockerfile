FROM ubuntu:14.04

RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y vim curl git unzip
RUN apt-get install -y php5-cli php5-curl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

WORKDIR /home/magephp
