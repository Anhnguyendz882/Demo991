FROM php:8.2-apache
COPY . /var/www/html/
# Cấp quyền ghi cho file database
RUN chmod 777 /var/www/html/database.txt
EXPOSE 80
