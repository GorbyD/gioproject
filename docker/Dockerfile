FROM php:8.1-fpm

ARG USER
ARG USER_ID
ARG GROUP_ID

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git mc \
    zip \
    unzip \
    curl \
    vim \
    libicu-dev

#Версия Gio - deprecated, не работает
#RUN curl -sL https://deb.nodesource.com/setup_16.x | bash \
#    && apt-get install nodejs -y

#По официальной инструкции установка npm не предлагается, на нативной системе все поставилось нормально, проблема почему то именно в докере
#И не по неизвестным причинам несмотря на то что я указал 16 версию - ставится 18
#Скорее всего node надо будет поднимать в отдельном сервисе docker
RUN apt-get install -y ca-certificates curl gnupg \
    && mkdir -p /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_16.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list \
    && apt-get update && apt-get install -y nodejs npm

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-configure intl
RUN docker-php-ext-install pdo pdo_mysql intl

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY xdebug.ini "${PHP_INI_DIR}/conf.d"

#На Ubuntu в этом месте была проблема. Если сделать sudo su - то видимо пользователь передается как "root",
#для него не создавалась группа и далее была ошибка при добавлении пользователя в группу
#поэтому в docker-compose нужно поменять передаваемый аргумент на нужную строку, означающее имя пользователя
#Также непонятно, почему создаваемому пользователю присваивается именно такой id, возможно чтобы он был одинаковый на хосте и в контейнере?
RUN groupadd --force -g $GROUP_ID $USER
RUN useradd -ms /bin/bash --no-user-group -g $GROUP_ID -u 1337 $USER
RUN usermod -u $USER_ID $USER

USER $USER


### ПОЛЕЗНЫЕ КОМАНДЫ ###
#docker stop $(docker ps -a -q) \
#&& docker rm $(docker ps -a -q) \
#&& docker rmi $(docker images -a -q)

#docker exec -it expennies-app /bin/bash

