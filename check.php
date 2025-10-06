<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");

if (!isset($_FILES['cert'])) {
    echo json_encode(['error' => 'Chưa upload file chứng chỉ!']);
    exit;
}

$file = $_FILES['cert']['tmp_name'];
$password = $_POST['password'] ?? '';

$certs = [];
if (!openssl_pkcs12_read(file_get_contents($file), $certs, $password)) {
    echo json_encode(['error' => 'Không đọc được file .p12 (sai mật khẩu hoặc file lỗi)']);
    exit;
}

// Xuất chứng chỉ ra file tạm
file_put_contents("cert.pem", $certs['cert']);

// Lấy thông tin cơ bản
$cert_data = openssl_x509_parse($certs['cert']);
$name = $cert_data['subject']['CN'] ?? 'Không rõ';
$type = $cert_data['issuer']['CN'] ?? 'Không rõ';
$valid_to = date('Y-m-d H:i:s', $cert_data['validTo_time_t']);
$expired = $cert_data['validTo_time_t'] < time();

// Lấy OCSP URL (để hỏi Apple)
$ocsp_url = trim(shell_exec("openssl x509 -noout -ocsp_uri -in cert.pem"));
$status = "Không rõ";

// Nếu có OCSP URL thì kiểm tra với Apple
if ($ocsp_url) {
    $ocsp_result = shell_exec("openssl ocsp -issuer cert.pem -cert cert.pem -url $ocsp_url -no_nonce 2>&1");

    if (strpos($ocsp_result, 'good') !== false) {
        $status = '✅ Hợp lệ (Apple xác nhận)';
    } elseif (strpos($ocsp_result, 'revoked') !== false) {
        $status = '❌ Đã bị thu hồi (revoked)';
    } else {
        $status = '⚠️ Không xác định (OCSP lỗi hoặc Apple không phản hồi)';
    }
} else {
    $status = $expired ? '❌ Hết hạn' : '✅ Còn hạn';
}

echo json_encode([
    'Tên chứng chỉ' => $name,
    'Loại chứng chỉ' => $type,
    'Ngày hết hạn' => $valid_to,
    'Trạng thái' => $status,
    'OCSP server' => $ocsp_url
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
