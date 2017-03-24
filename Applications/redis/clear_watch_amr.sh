#! /bin/bash
find /var/www/html/core/media/childwatch -mtime +2 -name "*" -exec rm {} \;

