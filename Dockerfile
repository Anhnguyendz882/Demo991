FROM php:8.2-apache
# Copy mọi file vào thư mục web
COPY . /var/www/html/
# Tạo file và cấp quyền cao nhất (777) cho toàn bộ thư mục web
RUN touch /var/www/html/database.txt && chmod -R 777 /var/www/html/
EXPOSE 80
