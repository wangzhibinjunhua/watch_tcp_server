#! /bin/bash
find /var/www/html/core/media/hawatch -mtime +2 -name "*" -exec rm {} \;

