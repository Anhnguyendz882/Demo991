FROM php:8.2-apache
# Copy code vào thư mục web
COPY . /var/www/html/
# Tự tạo file database nếu thiếu và cấp quyền 777
RUN touch /var/www/html/database.txt && chmod 777 /var/www/html/database.txt
EXPOSE 80
