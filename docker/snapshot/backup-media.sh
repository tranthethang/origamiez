#!/bin/sh

cd /var/www/html/wp-content/
tar -czvf uploads.tar.xz uploads &&
mv /var/www/html/wp-content/uploads.tar.xz /tmp/snapshot
