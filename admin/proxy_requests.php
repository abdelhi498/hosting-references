<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'update_status') {
    if (csrf_verify()) {
        $pdo->prepare("UPDATE proxy_requests SET status=?, admin_notes=? WHERE id=?")
            ->execute([$_POST['status'], trim($_POST['admin_notes'] ?? '') ?: null, (int)$_POST['id']]);
        flash_set('success', t('admin.saved'));
    }
    redirect('proxy_requests.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'delete') {
    if (csrf_verify()) {
        $pdo->prepare("DELETE FROM proxy_requests WHERE id = ?")->execute([(int)$_POST['id']]);
        flash_set('success', t('admin.deleted'));
    }
    redirect('proxy_requests.php');
}

$requests = $pdo->query("SELECT pr.*, co.name AS company_name FROM proxy_requests pr
                          LEFT JOIN companies co ON co.id = pr.company_id
                          ORDER BY pr.created_at DESC")->fetchAll();

$statuses = ['pending' => 'قيد الانتظار', 'contacted' => 'تم التواصل', 'paid' => 'تم الدفع', 'delivered' => 'تم التسليم', 'cancelled' => 'ملغي'];

$page_title = t('admin.proxy_requests');
require __DIR__ . '/includes/layout_start.php';
?>

<div class="table-wrap">
  <table class="admin-table">
    <thead><tr><th>الاسم</th><th>الهاتف</th><th>الشركة</th><th>الخطة</th><th>الحالة</th><th>التاريخ</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($requests as $r): ?>
        <tr>
          <td><?= e($r['name']) ?><?php if ($r['email']): ?><br><small style="color:var(--muted)"><?= e($r['email']) ?></small><?php endif; ?></td>
          <td><?= e($r['phone']) ?></td>
          <td><?= e($r['company_name'] ?? '-') ?></td>
          <td><?= e($r['plan_desired'] ?? '-') ?></td>
          <td>
            <form method="post" style="display:flex;gap:6px;align-items:center">
              <?= csrf_field() ?>
              <input type="hidden" name="do" value="update_status">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <select name="status" onchange="this.form.querySelector('button').disabled=false" style="padding:6px 8px;font-size:13px">
                <?php foreach ($statuses as $val => $label): ?>
                  <option value="<?= e($val) ?>" <?= $r['status'] === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
              <input type="text" name="admin_notes" value="<?= e($r['admin_notes'] ?? '') ?>" placeholder="ملاحظة" style="width:110px;padding:6px 8px;font-size:13px">
              <button class="btn btn-primary btn-sm">حفظ</button>
            </form>
          </td>
          <td><?= e(date('Y-m-d', strtotime($r['created_at']))) ?></td>
          <td>
            <form method="post" onsubmit="return confirm('<?= t('admin.confirm_delete') ?>')">
              <?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn btn-danger btn-sm"><?= t('admin.delete') ?></button>
            </form>
          </td>
        </tr>
        <?php if ($r['notes']): ?>
          <tr><td colspan="7" style="color:var(--muted);font-size:13px;background:var(--paper)">ملاحظات العميل: <?= e($r['notes']) ?></td></tr>
        <?php endif; ?>
      <?php endforeach; ?>
      <?php if (!$requests): ?><tr><td colspan="7" class="empty-state">لا توجد طلبات بعد</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/layout_end.php'; ?>
