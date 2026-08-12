<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$errors = [];

function lines_to_json(string $text): string {
    $lines = array_filter(array_map('trim', explode("\n", $text)), fn($l) => $l !== '');
    return json_encode(array_values($lines), JSON_UNESCAPED_UNICODE);
}
function json_to_lines(?string $json): string {
    $arr = json_decode((string)$json, true);
    return is_array($arr) ? implode("\n", $arr) : '';
}

// ---- Handle save (create or update) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'save') {
    if (!csrf_verify()) {
        $errors[] = t('form.error');
    } else {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: slugify($name);
        $data = [
            $slug,
            $name,
            trim($_POST['logo'] ?? ''),
            trim($_POST['website_url'] ?? ''),
            trim($_POST['affiliate_url'] ?? ''),
            (float)($_POST['trust_score'] ?? 8.0),
            trim($_POST['summary_ar'] ?? ''),
            trim($_POST['summary_en'] ?? ''),
            lines_to_json($_POST['pros_ar'] ?? ''),
            lines_to_json($_POST['pros_en'] ?? ''),
            lines_to_json($_POST['cons_ar'] ?? ''),
            lines_to_json($_POST['cons_en'] ?? ''),
            trim($_POST['starting_price'] ?? ''),
            trim($_POST['price_note_ar'] ?? '') ?: null,
            trim($_POST['price_note_en'] ?? '') ?: null,
            isset($_POST['is_featured']) ? 1 : 0,
            isset($_POST['is_active']) ? 1 : 0,
            (int)($_POST['sort_order'] ?? 0),
        ];

        if ($name === '' || trim($_POST['affiliate_url'] ?? '') === '') {
            $errors[] = t('form.error');
        }

        if (!$errors) {
            $edit_id = (int)($_POST['id'] ?? 0);
            if ($edit_id) {
                $sql = "UPDATE companies SET slug=?, name=?, logo=?, website_url=?, affiliate_url=?, trust_score=?,
                        summary_ar=?, summary_en=?, pros_ar=?, pros_en=?, cons_ar=?, cons_en=?,
                        starting_price=?, price_note_ar=?, price_note_en=?, is_featured=?, is_active=?, sort_order=?
                        WHERE id=?";
                $pdo->prepare($sql)->execute([...$data, $edit_id]);
            } else {
                $sql = "INSERT INTO companies (slug, name, logo, website_url, affiliate_url, trust_score,
                        summary_ar, summary_en, pros_ar, pros_en, cons_ar, cons_en,
                        starting_price, price_note_ar, price_note_en, is_featured, is_active, sort_order)
                        VALUES (" . implode(',', array_fill(0, count($data), '?')) . ")";
                $pdo->prepare($sql)->execute($data);
            }
            flash_set('success', t('admin.saved'));
            redirect('companies.php');
        }
    }
}

// ---- Handle delete ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'delete') {
    if (csrf_verify()) {
        $pdo->prepare("DELETE FROM companies WHERE id = ?")->execute([(int)$_POST['id']]);
        flash_set('success', t('admin.deleted'));
    }
    redirect('companies.php');
}

// ---- Load record for edit ----
$editing = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
    $stmt->execute([$id]);
    $editing = $stmt->fetch();
}

$companies = $pdo->query("SELECT * FROM companies ORDER BY sort_order")->fetchAll();

$page_title = t('admin.companies');
require __DIR__ . '/includes/layout_start.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
  <div class="panel">
    <h3 style="margin-top:0"><?= $editing ? t('admin.edit') : t('admin.add') ?> — <?= t('admin.companies') ?></h3>
    <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="do" value="save">
      <input type="hidden" name="id" value="<?= (int)($editing['id'] ?? 0) ?>">
      <div class="form-grid">
        <div class="field"><label>الاسم *</label><input type="text" name="name" required value="<?= e($editing['name'] ?? '') ?>"></div>
        <div class="field"><label>Slug (رابط)</label><input type="text" name="slug" value="<?= e($editing['slug'] ?? '') ?>" placeholder="يُنشأ تلقائيًا إن ترك فارغًا"></div>
      </div>
      <div class="form-grid">
        <div class="field"><label>موقع الشركة</label><input type="text" name="website_url" value="<?= e($editing['website_url'] ?? '') ?>"></div>
        <div class="field"><label>رابط الأفلييت *</label><input type="text" name="affiliate_url" required value="<?= e($editing['affiliate_url'] ?? '') ?>"></div>
      </div>
      <div class="form-grid">
        <div class="field"><label>اسم ملف الشعار (في assets/img/logos)</label><input type="text" name="logo" value="<?= e($editing['logo'] ?? '') ?>"></div>
        <div class="field"><label>درجة الثقة (0-10)</label><input type="number" step="0.1" min="0" max="10" name="trust_score" value="<?= e($editing['trust_score'] ?? '8.0') ?>"></div>
      </div>
      <div class="form-grid">
        <div class="field"><label>السعر يبدأ من</label><input type="text" name="starting_price" value="<?= e($editing['starting_price'] ?? '') ?>"></div>
        <div class="field"><label>ترتيب الظهور</label><input type="number" name="sort_order" value="<?= e($editing['sort_order'] ?? '0') ?>"></div>
      </div>
      <div class="form-grid">
        <div class="field"><label>ملاحظة السعر (عربي)</label><input type="text" name="price_note_ar" value="<?= e($editing['price_note_ar'] ?? '') ?>"></div>
        <div class="field"><label>Price note (English)</label><input type="text" name="price_note_en" value="<?= e($editing['price_note_en'] ?? '') ?>"></div>
      </div>
      <div class="form-grid">
        <div class="field"><label>ملخص المراجعة (عربي)</label><textarea name="summary_ar"><?= e($editing['summary_ar'] ?? '') ?></textarea></div>
        <div class="field"><label>Review summary (English)</label><textarea name="summary_en"><?= e($editing['summary_en'] ?? '') ?></textarea></div>
      </div>
      <div class="form-grid">
        <div class="field"><label>المميزات (سطر لكل ميزة) — عربي</label><textarea name="pros_ar" placeholder="سعر منافس&#10;سرعة عالية"><?= e(json_to_lines($editing['pros_ar'] ?? null)) ?></textarea></div>
        <div class="field"><label>Pros (one per line) — English</label><textarea name="pros_en"><?= e(json_to_lines($editing['pros_en'] ?? null)) ?></textarea></div>
      </div>
      <div class="form-grid">
        <div class="field"><label>العيوب (سطر لكل عيب) — عربي</label><textarea name="cons_ar"><?= e(json_to_lines($editing['cons_ar'] ?? null)) ?></textarea></div>
        <div class="field"><label>Cons (one per line) — English</label><textarea name="cons_en"><?= e(json_to_lines($editing['cons_en'] ?? null)) ?></textarea></div>
      </div>
      <div class="form-grid">
        <label class="checkbox-row"><input type="checkbox" name="is_featured" <?= !empty($editing['is_featured']) ? 'checked' : '' ?>> شركة مميزة (Featured)</label>
        <label class="checkbox-row"><input type="checkbox" name="is_active" <?= ($editing === null || !empty($editing['is_active'])) ? 'checked' : '' ?>> نشطة (تظهر في الموقع)</label>
      </div>
      <div style="margin-top:18px;display:flex;gap:10px">
        <button type="submit" class="btn btn-primary"><?= t('admin.save') ?></button>
        <a href="companies.php" class="btn btn-ghost">إلغاء</a>
      </div>
    </form>
  </div>

<?php else: ?>
  <div class="toolbar">
    <div></div>
    <a href="companies.php?action=add" class="btn btn-primary"><?= t('admin.add') ?></a>
  </div>
  <table class="admin-table">
    <thead><tr><th>الاسم</th><th>Slug</th><th>الثقة</th><th>السعر</th><th>مميزة</th><th>نشطة</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($companies as $c): ?>
        <tr>
          <td><?= e($c['name']) ?></td>
          <td><?= e($c['slug']) ?></td>
          <td><?= e($c['trust_score']) ?></td>
          <td><?= e($c['starting_price']) ?></td>
          <td><?= $c['is_featured'] ? '✔' : '—' ?></td>
          <td><?= $c['is_active'] ? '✔' : '—' ?></td>
          <td class="row-actions">
            <a href="companies.php?action=edit&id=<?= (int)$c['id'] ?>" class="btn btn-ghost btn-sm"><?= t('admin.edit') ?></a>
            <form method="post" onsubmit="return confirm('<?= t('admin.confirm_delete') ?>')" style="display:inline">
              <?= csrf_field() ?>
              <input type="hidden" name="do" value="delete">
              <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm"><?= t('admin.delete') ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout_end.php'; ?>
