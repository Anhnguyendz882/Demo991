FROM php:8.2-apache

# 1. Copy toàn bộ file từ GitHub vào thư mục web trước
COPY . /var/www/html/

# 2. Sau khi file đã ở đó rồi mới cấp quyền ghi cho database
RUN chmod 777 /var/www/html/database.txt

# 3. Mở cổng 80
EXPOSE 80
