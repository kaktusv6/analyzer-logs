#!/bin/sh

# Fix permissions for Linux users
SRC_DIR=/var/www

USER=www-data
GROUP=www-data

uid=$(stat -c '%u' $SRC_DIR)
gid=$(stat -c '%g' $SRC_DIR)

echo $uid > /root/uid
echo $gid > /root/gid

usermod -u $uid $USER
groupmod -g $gid $GROUP

chown -R $USER:$GROUP $SRC_DIR

exec php -S localhost:8080
