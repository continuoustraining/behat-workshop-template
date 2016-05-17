FROM ubuntu:16.04
MAINTAINER pascal.paulis@continuousphp.com

RUN apt-get update && apt-get install -y openssh-server apache2 supervisor wget libfreetype6 libfontconfig bzip2

RUN mkdir -p /srv/var && \
  wget -q --no-check-certificate -O /tmp/phantomjs-2.1.1-linux-x86_64.tar.bz2 https://bitbucket.org/ariya/phantomjs/downloads/phantomjs-2.1.1-linux-x86_64.tar.bz2 && \
  tar -xjf /tmp/phantomjs-2.1.1-linux-x86_64.tar.bz2 -C /tmp && \
  rm -f /tmp/phantomjs-2.1.1-linux-x86_64.tar.bz2 && \
  mv /tmp/phantomjs-2.1.1-linux-x86_64/ /srv/var/phantomjs && \
  ln -s /srv/var/phantomjs/bin/phantomjs /usr/bin/phantomjs

RUN mkdir -p /var/lock/apache2 /var/run/apache2 /var/run/sshd /var/log/supervisor

RUN apt-get update
RUN apt-get -y install sudo
RUN useradd -m -s /bin/bash docker && echo "docker:docker" | chpasswd && adduser docker sudo
RUN sudo sed -i 's;www-data:x:33:33:www-data:/var/www:/usr/sbin/nologin;www-data:x:33:33:www-data:/var/www:/bin/bash;' /etc/passwd

RUN apt-get -y install aptitude vim dialog php-common php-cli php7.0-cli php7.0-common php7.0-json \
  php7.0-opcache php7.0-readline psmisc ucf libapache2-mod-php php-mbstring php-curl php-xml zip unzip \
  php-sqlite3

RUN a2enmod rewrite
RUN rm /etc/apache2/sites-available/000-default.conf
RUN echo '<VirtualHost *:80>' >> /etc/apache2/sites-available/000-default.conf
RUN echo '    DocumentRoot /var/www/public' >> /etc/apache2/sites-available/000-default.conf
RUN echo '    <Directory "/var/www/public">' >> /etc/apache2/sites-available/000-default.conf
RUN echo '        AllowOverride All' >> /etc/apache2/sites-available/000-default.conf
RUN echo '        Require all granted' >> /etc/apache2/sites-available/000-default.conf
RUN echo '    </Directory>' >> /etc/apache2/sites-available/000-default.conf
RUN echo '    ErrorLog ${APACHE_LOG_DIR}/error.log' >> /etc/apache2/sites-available/000-default.conf
RUN echo '    CustomLog ${APACHE_LOG_DIR}/access.log combined' >> /etc/apache2/sites-available/000-default.conf
RUN echo '</VirtualHost>' >> /etc/apache2/sites-available/000-default.conf

RUN service apache2 restart

RUN sudo locale-gen en_US.UTF-8 && sudo locale-gen en_US && update-locale LANG=en_US.UTF-8

RUN chown -R www-data /var/www/*
RUN chown -R www-data /var/www/.*
RUN chmod -R 775 /var/www/*
RUN chmod -R 775 /var/www/.*

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 22 80

CMD ["/usr/bin/supervisord"]