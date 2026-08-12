<?php
require_once __DIR__ . '/includes/bootstrap.php';

$slug = $_GET['company'] ?? '';
$coupon_id = isset($_GET['coupon']) ? (int)$_GET['coupon'] : null;

$stmt = $pdo->prepare("SELECT * FROM companies WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$company = $stmt->fetch();

if (!$company) {
    http_response_code(404);
    die('Company not found.');
}

$destination = $company['affiliate_url'];

if ($coupon_id) {
    $cstmt = $pdo->prepare("SELECT redirect_url FROM coupons WHERE id = ? AND company_id = ?");
    $cstmt->execute([$coupon_id, $company['id']]);
    $couponRow = $cstmt->fetch();
    if ($couponRow && !empty($couponRow['redirect_url'])) {
        $destination = $couponRow['redirect_url'];
    }
}

// Log the click
$pdo->prepare("INSERT INTO clicks (company_id, coupon_id, ip_address) VALUES (?, ?, ?)")
    ->execute([$company['id'], $coupon_id, client_ip()]);

if ($coupon_id) {
    $pdo->prepare("UPDATE coupons SET clicks = clicks + 1 WHERE id = ?")->execute([$coupon_id]);
}

header('Location: ' . $destination);
exit;
