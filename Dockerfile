FROM php:8.2-apache
# Copy mọi thứ vào thư mục web
COPY . /var/www/html/
# Tạo file database và mở quyền ghi (777) để lưu Key
RUN touch /var/www/html/database.txt && chmod 777 /var/www/html/database.txt
# Chạy trên cổng 80
EXPOSE 80
