<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_role('super_admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'add') {
    if (csrf_verify()) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($name && $email && strlen($password) >= 8) {
            $pdo->prepare("INSERT INTO admins (name, email, password_hash, role) VALUES (?,?,?,?)")
                ->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $_POST['role'] === 'super_admin' ? 'super_admin' : 'editor']);
            flash_set('success', t('admin.saved'));
        } else {
            flash_set('error', t('form.error'));
        }
    }
    redirect('admins.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'toggle_active') {
    if (csrf_verify() && (int)$_POST['id'] !== (int)$_SESSION['admin_id']) {
        $pdo->prepare("UPDATE admins SET is_active = 1 - is_active WHERE id=?")->execute([(int)$_POST['id']]);
    }
    redirect('admins.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'delete') {
    if (csrf_verify() && (int)$_POST['id'] !== (int)$_SESSION['admin_id']) {
        $pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([(int)$_POST['id']]);
        flash_set('success', t('admin.deleted'));
    }
    redirect('admins.php');
}

$admins = $pdo->query("SELECT * FROM admins ORDER BY created_at")->fetchAll();
$page_title = t('admin.admins');
require __DIR__ . '/includes/layout_start.php';
?>

<div class="panel" style="margin-bottom:26px">
  <h3 style="margin-top:0">إضافة مسؤول</h3>
  <form method="post" class="form-grid">
    <?= csrf_field() ?>
    <input type="hidden" name="do" value="add">
    <div class="field"><label>الاسم *</label><input type="text" name="name" required></div>
    <div class="field"><label>البريد الإلكتروني *</label><input type="email" name="email" required></div>
    <div class="field"><label>كلمة المرور * (8 أحرف فأكثر)</label><input type="password" name="password" required minlength="8"></div>
    <div class="field">
      <label>الدور</label>
      <select name="role"><option value="editor">محرر (Editor)</option><option value="super_admin">مسؤول رئيسي (Super Admin)</option></select>
    </div>
    <div style="align-self:end"><button class="btn btn-primary"><?= t('admin.add') ?></button></div>
  </form>
</div>

<table class="admin-table">
  <thead><tr><th>الاسم</th><th>البريد</th><th>الدور</th><th>مفعّل</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($admins as $a): ?>
      <tr>
        <td><?= e($a['name']) ?></td>
        <td><?= e($a['email']) ?></td>
        <td><?= $a['role'] === 'super_admin' ? 'مسؤول رئيسي' : 'محرر' ?></td>
        <td><?= $a['is_active'] ? '✔' : '—' ?></td>
        <td class="row-actions">
          <?php if ((int)$a['id'] !== (int)$_SESSION['admin_id']): ?>
            <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="do" value="toggle_active"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>"><button class="btn btn-ghost btn-sm"><?= $a['is_active'] ? 'تعطيل' : 'تفعيل' ?></button></form>
            <form method="post" style="display:inline" onsubmit="return confirm('<?= t('admin.confirm_delete') ?>')"><?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>"><button class="btn btn-danger btn-sm"><?= t('admin.delete') ?></button></form>
          <?php else: ?>
            <span style="color:var(--muted);font-size:13px">(أنت)</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require __DIR__ . '/includes/layout_end.php'; ?>
