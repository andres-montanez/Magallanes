FROM ubuntu:17.10

RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y vim curl git unzip
RUN apt-get install -y php7.1-cli php-zip php7.1-curl php7.1-xml php7.1-mbstring

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

WORKDIR /home/magephp
