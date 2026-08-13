<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (admin_logged_in()) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = t('form.error');
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_role'] = $admin['role'];
            redirect('dashboard.php');
        } else {
            $error = t('admin.login_error');
        }
    }
}
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= t('admin.login_title') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../<?= e(asset_url('assets/css/style.css')) ?>">
<style>
body.lang-ar{display:flex;align-items:center;justify-content:center;min-height:100vh;background:var(--navy-950)}
.login-card{width:100%;max-width:400px;background:#fff;border-radius:var(--radius);padding:34px;box-shadow:var(--shadow-lg)}
.login-card .mark{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,var(--emerald),var(--navy-700));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900;margin-bottom:18px}
</style>
</head>
<body class="lang-ar">
  <div class="login-card">
    <div class="mark">HR</div>
    <h2 style="margin-top:0"><?= t('admin.login_title') ?></h2>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="field"><label><?= t('admin.email') ?></label><input type="email" name="email" required autofocus></div>
      <div class="field"><label><?= t('admin.password') ?></label><input type="password" name="password" required></div>
      <button type="submit" class="btn btn-primary btn-block"><?= t('admin.login_btn') ?></button>
    </form>
  </div>
</body>
</html>
