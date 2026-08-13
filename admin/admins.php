<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_role('super_admin');

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$errors = [];

function is_last_super_admin(PDO $pdo, int $admin_id): bool
{
    $stmt = $pdo->prepare("SELECT role FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    if ($stmt->fetchColumn() !== 'super_admin') {
        return false;
    }
    $count = (int)$pdo->query("SELECT COUNT(*) FROM admins WHERE role = 'super_admin'")->fetchColumn();
    return $count <= 1;
}

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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'edit') {
    if (!csrf_verify()) {
        $errors[] = t('form.error');
    } else {
        $edit_id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] === 'super_admin' ? 'super_admin' : 'editor';

        if (!$edit_id || $name === '' || $email === '') {
            $errors[] = t('form.error');
        } else {
            $dup = $pdo->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
            $dup->execute([$email, $edit_id]);
            if ($dup->fetch()) {
                $errors[] = t('admin.email_taken');
            } elseif ($role !== 'super_admin' && is_last_super_admin($pdo, $edit_id)) {
                $errors[] = t('admin.last_super_admin');
            }
        }

        if (!$errors) {
            $pdo->prepare("UPDATE admins SET name = ?, email = ?, role = ? WHERE id = ?")
                ->execute([$name, $email, $role, $edit_id]);
            if ($edit_id === (int)$_SESSION['admin_id']) {
                $_SESSION['admin_name'] = $name;
                $_SESSION['admin_role'] = $role;
            }
            flash_set('success', t('admin.saved'));
            redirect('admins.php');
        }
        $action = 'edit';
        $id = $edit_id;
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'toggle_active') {
    $target_id = (int)$_POST['id'];
    if (csrf_verify() && $target_id !== (int)$_SESSION['admin_id']) {
        $stmt = $pdo->prepare("SELECT is_active FROM admins WHERE id = ?");
        $stmt->execute([$target_id]);
        $currently_active = (bool)$stmt->fetchColumn();
        if ($currently_active && is_last_super_admin($pdo, $target_id)) {
            flash_set('error', t('admin.last_super_admin'));
        } else {
            $pdo->prepare("UPDATE admins SET is_active = 1 - is_active WHERE id=?")->execute([$target_id]);
        }
    }
    redirect('admins.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'delete') {
    $target_id = (int)$_POST['id'];
    if (csrf_verify() && $target_id !== (int)$_SESSION['admin_id']) {
        if (is_last_super_admin($pdo, $target_id)) {
            flash_set('error', t('admin.last_super_admin'));
        } else {
            $pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([$target_id]);
            flash_set('success', t('admin.deleted'));
        }
    }
    redirect('admins.php');
}

$editing = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$id]);
    $editing = $stmt->fetch();
}

$admins = $pdo->query("SELECT * FROM admins ORDER BY created_at")->fetchAll();
$page_title = t('admin.admins');
require __DIR__ . '/includes/layout_start.php';
?>

<?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

<?php if ($editing): ?>
  <div class="panel" style="margin-bottom:26px">
    <h3 style="margin-top:0"><?= t('admin.edit') ?> — <?= t('admin.admins') ?></h3>
    <form method="post" class="form-grid">
      <?= csrf_field() ?>
      <input type="hidden" name="do" value="edit">
      <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
      <div class="field"><label>الاسم *</label><input type="text" name="name" required value="<?= e($editing['name']) ?>"></div>
      <div class="field"><label>البريد الإلكتروني *</label><input type="email" name="email" required value="<?= e($editing['email']) ?>"></div>
      <div class="field">
        <label><?= t('admin.role') ?></label>
        <select name="role">
          <option value="editor" <?= $editing['role'] === 'editor' ? 'selected' : '' ?>><?= t('admin.role_editor') ?></option>
          <option value="super_admin" <?= $editing['role'] === 'super_admin' ? 'selected' : '' ?>><?= t('admin.role_super_admin') ?></option>
        </select>
      </div>
      <div style="align-self:end;display:flex;gap:10px">
        <button type="submit" class="btn btn-primary"><?= t('admin.save') ?></button>
        <a href="admins.php" class="btn btn-ghost">إلغاء</a>
      </div>
    </form>
  </div>
<?php else: ?>
  <div class="panel" style="margin-bottom:26px">
    <h3 style="margin-top:0">إضافة مسؤول</h3>
    <form method="post" class="form-grid">
      <?= csrf_field() ?>
      <input type="hidden" name="do" value="add">
      <div class="field"><label>الاسم *</label><input type="text" name="name" required></div>
      <div class="field"><label>البريد الإلكتروني *</label><input type="email" name="email" required></div>
      <div class="field"><label>كلمة المرور * (8 أحرف فأكثر)</label><input type="password" name="password" required minlength="8"></div>
      <div class="field">
        <label><?= t('admin.role') ?></label>
        <select name="role"><option value="editor"><?= t('admin.role_editor') ?></option><option value="super_admin"><?= t('admin.role_super_admin') ?></option></select>
      </div>
      <div style="align-self:end"><button class="btn btn-primary"><?= t('admin.add') ?></button></div>
    </form>
  </div>
<?php endif; ?>

<table class="admin-table">
  <thead><tr><th>الاسم</th><th>البريد</th><th>الدور</th><th>مفعّل</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($admins as $a): ?>
      <tr>
        <td><?= e($a['name']) ?></td>
        <td><?= e($a['email']) ?></td>
        <td><?= $a['role'] === 'super_admin' ? t('admin.role_super_admin') : t('admin.role_editor') ?></td>
        <td><?= $a['is_active'] ? '✔' : '—' ?></td>
        <td class="row-actions">
          <a href="admins.php?action=edit&id=<?= (int)$a['id'] ?>" class="btn btn-ghost btn-sm"><?= t('admin.edit') ?></a>
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
