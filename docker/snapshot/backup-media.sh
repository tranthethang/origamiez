#!/bin/sh

tar -czvf /var/www/html/wp-content/uploads.tar.xz /var/www/html/wp-content/uploads &&
mv /var/www/html/wp-content/uploads.tar.xz /tmp/snapshot
