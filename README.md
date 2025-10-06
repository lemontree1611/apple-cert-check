# 🍏 Apple Certificate Checker

Dự án kiểm tra thông tin và trạng thái chứng chỉ Apple (.p12):
- Đọc thông tin từ chứng chỉ
- Xác định còn hạn hay hết hạn
- Gọi OCSP Apple để biết có bị thu hồi (revoked) hay không

## Triển khai trên Render
1. Fork repo này
2. Vào [https://render.com](https://render.com)
3. New → Web Service → chọn repo này
4. Render sẽ build Dockerfile và tự host PHP server
5. Truy cập URL Render để upload file `.p12`
