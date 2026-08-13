<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$errors = [];
$account_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'change_password') {
    if (!csrf_verify()) {
        $errors[] = t('form.error');
    } else {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($current, $row['password_hash'])) {
            $errors[] = t('admin.current_password_wrong');
        } elseif (strlen($new) < 8) {
            $errors[] = t('admin.password_too_short');
        } elseif ($new !== $confirm) {
            $errors[] = t('admin.password_mismatch');
        }

        if (!$errors) {
            $pdo->prepare("UPDATE admins SET password_hash = ? WHERE id = ?")
                ->execute([password_hash($new, PASSWORD_DEFAULT), $_SESSION['admin_id']]);
            flash_set('success', t('admin.password_changed'));
            redirect('profile.php');
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'update_account') {
    if (!csrf_verify()) {
        $account_errors[] = t('form.error');
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $current = $_POST['current_password_for_account'] ?? '';

        $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($current, $row['password_hash'])) {
            $account_errors[] = t('admin.current_password_wrong');
        } elseif ($name === '' || $email === '') {
            $account_errors[] = t('form.error');
        } else {
            $dup = $pdo->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
            $dup->execute([$email, $_SESSION['admin_id']]);
            if ($dup->fetch()) {
                $account_errors[] = t('admin.email_taken');
            }
        }

        if (!$account_errors) {
            $pdo->prepare("UPDATE admins SET name = ?, email = ? WHERE id = ?")
                ->execute([$name, $email, $_SESSION['admin_id']]);
            $_SESSION['admin_name'] = $name;
            flash_set('success', t('admin.account_updated'));
            redirect('profile.php');
        }
    }
}

$stmt = $pdo->prepare("SELECT name, email FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$me = $stmt->fetch();

$page_title = t('admin.change_password');
require __DIR__ . '/includes/layout_start.php';
?>

<div class="panel" style="max-width:480px;margin-bottom:26px">
  <h3 style="margin-top:0"><?= t('admin.account_info') ?></h3>
  <?php foreach ($account_errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="do" value="update_account">
    <div class="field">
      <label><?= t('admin.name') ?></label>
      <input type="text" name="name" required value="<?= e($me['name'] ?? '') ?>">
    </div>
    <div class="field">
      <label><?= t('admin.email') ?></label>
      <input type="email" name="email" required value="<?= e($me['email'] ?? '') ?>">
    </div>
    <div class="field">
      <label><?= t('admin.current_password') ?></label>
      <input type="password" name="current_password_for_account" required autocomplete="current-password">
    </div>
    <button type="submit" class="btn btn-primary"><?= t('admin.save') ?></button>
  </form>
</div>

<div class="panel" style="max-width:480px">
  <h3 style="margin-top:0"><?= t('admin.change_password') ?></h3>
  <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="do" value="change_password">
    <div class="field">
      <label><?= t('admin.current_password') ?></label>
      <input type="password" name="current_password" required autocomplete="current-password">
    </div>
    <div class="field">
      <label><?= t('admin.new_password') ?></label>
      <input type="password" name="new_password" required minlength="8" autocomplete="new-password">
    </div>
    <div class="field">
      <label><?= t('admin.confirm_password') ?></label>
      <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password">
    </div>
    <button type="submit" class="btn btn-primary"><?= t('admin.save') ?></button>
  </form>
</div>

<?php require __DIR__ . '/includes/layout_end.php'; ?>
