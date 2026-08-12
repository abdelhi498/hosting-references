<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

// ---- Add / delete feature keys ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'add_key') {
    if (csrf_verify()) {
        $key = slugify($_POST['key_name'] ?? '');
        $pdo->prepare("INSERT INTO feature_keys (key_name, label_ar, label_en, sort_order) VALUES (?,?,?,?)")
            ->execute([$key, trim($_POST['label_ar'] ?? ''), trim($_POST['label_en'] ?? ''), (int)($_POST['sort_order'] ?? 0)]);
        flash_set('success', t('admin.saved'));
    }
    redirect('features.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'delete_key') {
    if (csrf_verify()) {
        $pdo->prepare("DELETE FROM feature_keys WHERE id = ?")->execute([(int)$_POST['id']]);
        flash_set('success', t('admin.deleted'));
    }
    redirect('features.php');
}

// ---- Save the full feature matrix (company x feature) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'save_matrix') {
    if (csrf_verify()) {
        $values = $_POST['val'] ?? []; // val[company_id][feature_id] = text
        $included = $_POST['inc'] ?? []; // inc[company_id][feature_id] = 1
        foreach ($values as $company_id => $byFeature) {
            foreach ($byFeature as $feature_id => $val) {
                $val = trim($val);
                $isIncluded = !empty($included[$company_id][$feature_id]) ? 1 : 0;
                $stmt = $pdo->prepare("INSERT INTO company_features (company_id, feature_key_id, value_ar, value_en, is_included)
                                       VALUES (?, ?, ?, ?, ?)
                                       ON DUPLICATE KEY UPDATE value_ar = VALUES(value_ar), is_included = VALUES(is_included)");
                $stmt->execute([(int)$company_id, (int)$feature_id, $val ?: null, $val ?: null, $isIncluded]);
            }
        }
        flash_set('success', t('admin.saved'));
    }
    redirect('features.php');
}

$feature_keys = $pdo->query("SELECT * FROM feature_keys ORDER BY sort_order")->fetchAll();
$companies = $pdo->query("SELECT id, name FROM companies ORDER BY sort_order")->fetchAll();
$rows = $pdo->query("SELECT * FROM company_features")->fetchAll();
$matrix = [];
foreach ($rows as $r) { $matrix[$r['company_id']][$r['feature_key_id']] = $r; }

$page_title = t('admin.features');
require __DIR__ . '/includes/layout_start.php';
?>

<div class="panel" style="margin-bottom:26px">
  <h3 style="margin-top:0">إضافة خاصية مقارنة جديدة</h3>
  <form method="post" class="form-grid">
    <?= csrf_field() ?>
    <input type="hidden" name="do" value="add_key">
    <div class="field"><label>الاسم بالعربي</label><input type="text" name="label_ar" required></div>
    <div class="field"><label>Name in English</label><input type="text" name="label_en" required></div>
    <div class="field"><label>مفتاح فريد (اختياري)</label><input type="text" name="key_name" placeholder="مثال: free_ssl"></div>
    <div class="field"><label>ترتيب</label><input type="number" name="sort_order" value="0"></div>
    <div style="align-self:end"><button class="btn btn-primary"><?= t('admin.add') ?></button></div>
  </form>
</div>

<div class="panel">
  <h3 style="margin-top:0">جدول قيم المقارنة</h3>
  <p style="color:var(--muted);font-size:14px">اكتب القيمة الظاهرة في العمود (مثل "100 GB")، وفعّل مربع "مضمّنة" إذا كانت الميزة متاحة بدون قيمة نصية (تظهر علامة ✔).</p>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="do" value="save_matrix">
    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>الشركة</th>
            <?php foreach ($feature_keys as $fk): ?>
              <th>
                <?= e($fk['label_ar']) ?>
                <form method="post" style="display:inline" onsubmit="return confirm('<?= t('admin.confirm_delete') ?>')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="do" value="delete_key">
                  <input type="hidden" name="id" value="<?= (int)$fk['id'] ?>">
                  <button class="btn btn-danger btn-sm" style="padding:2px 6px;margin-top:4px">×</button>
                </form>
              </th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($companies as $co): ?>
            <tr>
              <td><strong><?= e($co['name']) ?></strong></td>
              <?php foreach ($feature_keys as $fk):
                $cell = $matrix[$co['id']][$fk['id']] ?? null;
              ?>
                <td>
                  <input type="text" name="val[<?= (int)$co['id'] ?>][<?= (int)$fk['id'] ?>]" value="<?= e($cell['value_ar'] ?? '') ?>" style="width:120px;margin-bottom:6px">
                  <label class="checkbox-row" style="font-size:12px">
                    <input type="checkbox" name="inc[<?= (int)$co['id'] ?>][<?= (int)$fk['id'] ?>]" <?= empty($cell) || !empty($cell['is_included']) ? 'checked' : '' ?>>
                    مضمّنة
                  </label>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <button type="submit" class="btn btn-primary" style="margin-top:16px"><?= t('admin.save') ?></button>
  </form>
</div>

<?php require __DIR__ . '/includes/layout_end.php'; ?>
