#!/bin/sh

cd ~ &&
curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php &&
php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer &&
cd /var/www/html/wp-content/themes/origamiez &&
composer install