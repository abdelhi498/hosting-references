<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$errors = [];

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

$page_title = t('admin.change_password');
require __DIR__ . '/includes/layout_start.php';
?>

<div class="panel" style="max-width:480px">
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
