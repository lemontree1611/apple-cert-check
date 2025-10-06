# Dùng PHP 8.2 CLI có sẵn OpenSSL
FROM php:8.2-cli

# Cài OpenSSL CLI để kiểm tra OCSP
RUN apt-get update && apt-get install -y openssl

# Copy toàn bộ project vào container
WORKDIR /app
COPY . /app

# Render yêu cầu mở port 10000
EXPOSE 10000

# Chạy PHP built-in server
CMD ["php", "-S", "0.0.0.0:10000"]
