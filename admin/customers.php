<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'add') {
    if (csrf_verify()) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if ($name && $email) {
            $pdo->prepare("INSERT INTO customers (name, email, phone) VALUES (?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name)")
                ->execute([$name, $email, trim($_POST['phone'] ?? '') ?: null]);
            flash_set('success', t('admin.saved'));
        }
    }
    redirect('customers.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'toggle_ban') {
    if (csrf_verify()) {
        $stmt = $pdo->prepare("SELECT status FROM customers WHERE id=?");
        $stmt->execute([(int)$_POST['id']]);
        $status = $stmt->fetchColumn();
        $new = $status === 'banned' ? 'active' : 'banned';
        $pdo->prepare("UPDATE customers SET status=? WHERE id=?")->execute([$new, (int)$_POST['id']]);
        flash_set('success', t('admin.saved'));
    }
    redirect('customers.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'delete') {
    if (csrf_verify()) {
        $pdo->prepare("DELETE FROM customers WHERE id = ?")->execute([(int)$_POST['id']]);
        flash_set('success', t('admin.deleted'));
    }
    redirect('customers.php');
}

$customers = $pdo->query("SELECT * FROM customers ORDER BY created_at DESC")->fetchAll();

$page_title = t('admin.customers');
require __DIR__ . '/includes/layout_start.php';
?>

<div class="panel" style="margin-bottom:26px">
  <h3 style="margin-top:0">إضافة عضو</h3>
  <form method="post" class="form-grid">
    <?= csrf_field() ?>
    <input type="hidden" name="do" value="add">
    <div class="field"><label>الاسم *</label><input type="text" name="name" required></div>
    <div class="field"><label>البريد الإلكتروني *</label><input type="email" name="email" required></div>
    <div class="field"><label>الهاتف</label><input type="text" name="phone"></div>
    <div style="align-self:end"><button class="btn btn-primary"><?= t('admin.add') ?></button></div>
  </form>
</div>

<table class="admin-table">
  <thead><tr><th>الاسم</th><th>البريد</th><th>الهاتف</th><th>الحالة</th><th>تاريخ الانضمام</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($customers as $c): ?>
      <tr>
        <td><?= e($c['name']) ?></td>
        <td><?= e($c['email']) ?></td>
        <td><?= e($c['phone'] ?? '-') ?></td>
        <td><span class="<?= $c['status'] === 'banned' ? 'status-cancelled' : 'status-delivered' ?>"><?= $c['status'] === 'banned' ? 'محظور' : 'نشط' ?></span></td>
        <td><?= e(date('Y-m-d', strtotime($c['created_at']))) ?></td>
        <td class="row-actions">
          <form method="post" style="display:inline">
            <?= csrf_field() ?><input type="hidden" name="do" value="toggle_ban"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
            <button class="btn btn-ghost btn-sm"><?= $c['status'] === 'banned' ? 'إلغاء الحظر' : 'حظر' ?></button>
          </form>
          <form method="post" style="display:inline" onsubmit="return confirm('<?= t('admin.confirm_delete') ?>')">
            <?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
            <button class="btn btn-danger btn-sm"><?= t('admin.delete') ?></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$customers): ?><tr><td colspan="6" class="empty-state">لا يوجد أعضاء بعد</td></tr><?php endif; ?>
  </tbody>
</table>

<?php require __DIR__ . '/includes/layout_end.php'; ?>
