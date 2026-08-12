<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'save') {
    if (!csrf_verify()) {
        $errors[] = t('form.error');
    } else {
        $data = [
            (int)($_POST['company_id'] ?? 0),
            trim($_POST['code'] ?? ''),
            trim($_POST['title_ar'] ?? ''),
            trim($_POST['title_en'] ?? ''),
            trim($_POST['description_ar'] ?? '') ?: null,
            trim($_POST['description_en'] ?? '') ?: null,
            trim($_POST['discount_text'] ?? '') ?: null,
            trim($_POST['expires_at'] ?? '') ?: null,
            isset($_POST['is_active']) ? 1 : 0,
        ];
        if (!$data[0] || $data[1] === '' || $data[2] === '') $errors[] = t('form.error');

        if (!$errors) {
            $edit_id = (int)($_POST['id'] ?? 0);
            if ($edit_id) {
                $pdo->prepare("UPDATE coupons SET company_id=?, code=?, title_ar=?, title_en=?, description_ar=?, description_en=?,
                               discount_text=?, expires_at=?, is_active=? WHERE id=?")->execute([...$data, $edit_id]);
            } else {
                $pdo->prepare("INSERT INTO coupons (company_id, code, title_ar, title_en, description_ar, description_en,
                               discount_text, expires_at, is_active) VALUES (?,?,?,?,?,?,?,?,?)")->execute($data);
            }
            flash_set('success', t('admin.saved'));
            redirect('coupons.php');
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'delete') {
    if (csrf_verify()) {
        $pdo->prepare("DELETE FROM coupons WHERE id = ?")->execute([(int)$_POST['id']]);
        flash_set('success', t('admin.deleted'));
    }
    redirect('coupons.php');
}

$editing = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE id = ?");
    $stmt->execute([$id]);
    $editing = $stmt->fetch();
}

$companies = $pdo->query("SELECT id, name FROM companies ORDER BY sort_order")->fetchAll();
$coupons = $pdo->query("SELECT c.*, co.name AS company_name FROM coupons c JOIN companies co ON co.id=c.company_id ORDER BY c.created_at DESC")->fetchAll();

$page_title = t('admin.coupons');
require __DIR__ . '/includes/layout_start.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
  <div class="panel">
    <h3 style="margin-top:0"><?= $editing ? t('admin.edit') : t('admin.add') ?> — <?= t('admin.coupons') ?></h3>
    <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="do" value="save">
      <input type="hidden" name="id" value="<?= (int)($editing['id'] ?? 0) ?>">
      <div class="form-grid">
        <div class="field">
          <label>الشركة *</label>
          <select name="company_id" required>
            <option value=""><?= t('form.select') ?></option>
            <?php foreach ($companies as $co): ?>
              <option value="<?= (int)$co['id'] ?>" <?= (int)($editing['company_id'] ?? 0) === (int)$co['id'] ? 'selected' : '' ?>><?= e($co['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>كود الكوبون *</label><input type="text" name="code" required value="<?= e($editing['code'] ?? '') ?>"></div>
      </div>
      <div class="form-grid">
        <div class="field"><label>العنوان (عربي) *</label><input type="text" name="title_ar" required value="<?= e($editing['title_ar'] ?? '') ?>"></div>
        <div class="field"><label>Title (English) *</label><input type="text" name="title_en" required value="<?= e($editing['title_en'] ?? '') ?>"></div>
      </div>
      <div class="form-grid">
        <div class="field"><label>الوصف (عربي)</label><input type="text" name="description_ar" value="<?= e($editing['description_ar'] ?? '') ?>"></div>
        <div class="field"><label>Description (English)</label><input type="text" name="description_en" value="<?= e($editing['description_en'] ?? '') ?>"></div>
      </div>
      <div class="form-grid">
        <div class="field"><label>نص الخصم (مثل 50% OFF)</label><input type="text" name="discount_text" value="<?= e($editing['discount_text'] ?? '') ?>"></div>
        <div class="field"><label>تاريخ الانتهاء (اختياري)</label><input type="date" name="expires_at" value="<?= e($editing['expires_at'] ?? '') ?>"></div>
      </div>
      <label class="checkbox-row"><input type="checkbox" name="is_active" <?= ($editing === null || !empty($editing['is_active'])) ? 'checked' : '' ?>> نشط</label>
      <div style="margin-top:18px;display:flex;gap:10px">
        <button type="submit" class="btn btn-primary"><?= t('admin.save') ?></button>
        <a href="coupons.php" class="btn btn-ghost">إلغاء</a>
      </div>
    </form>
  </div>
<?php else: ?>
  <div class="toolbar"><div></div><a href="coupons.php?action=add" class="btn btn-primary"><?= t('admin.add') ?></a></div>
  <table class="admin-table">
    <thead><tr><th>الكود</th><th>الشركة</th><th>العنوان</th><th>الخصم</th><th>نشط</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($coupons as $cp): ?>
        <tr>
          <td><?= e($cp['code']) ?></td>
          <td><?= e($cp['company_name']) ?></td>
          <td><?= e($cp['title_ar']) ?></td>
          <td><?= e($cp['discount_text']) ?></td>
          <td><?= $cp['is_active'] ? '✔' : '—' ?></td>
          <td class="row-actions">
            <a href="coupons.php?action=edit&id=<?= (int)$cp['id'] ?>" class="btn btn-ghost btn-sm"><?= t('admin.edit') ?></a>
            <form method="post" onsubmit="return confirm('<?= t('admin.confirm_delete') ?>')" style="display:inline">
              <?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int)$cp['id'] ?>">
              <button class="btn btn-danger btn-sm"><?= t('admin.delete') ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout_end.php'; ?>
