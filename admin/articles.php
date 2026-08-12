<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$errors = [];

function handle_cover_upload(): ?string {
    if (empty($_FILES['cover_image']['name'])) return null;
    $allowed = ['jpg','jpeg','png','webp'];
    $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) return null;
    if ($_FILES['cover_image']['size'] > 4 * 1024 * 1024) return null;
    $filename = 'article-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    move_uploaded_file($_FILES['cover_image']['tmp_name'], UPLOADS_DIR . '/' . $filename);
    return $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'save') {
    if (!csrf_verify()) {
        $errors[] = t('form.error');
    } else {
        $title_ar = trim($_POST['title_ar'] ?? '');
        $title_en = trim($_POST['title_en'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: slugify($title_en ?: $title_ar);
        $cover = handle_cover_upload();

        $edit_id = (int)($_POST['id'] ?? 0);
        if ($edit_id && !$cover) {
            $stmt = $pdo->prepare("SELECT cover_image FROM articles WHERE id = ?");
            $stmt->execute([$edit_id]);
            $cover = $stmt->fetchColumn() ?: null;
        }

        $data = [
            $_POST['type'] === 'guide' ? 'guide' : 'blog',
            $slug, $title_ar, $title_en,
            trim($_POST['excerpt_ar'] ?? '') ?: null,
            trim($_POST['excerpt_en'] ?? '') ?: null,
            $_POST['content_ar'] ?? '',
            $_POST['content_en'] ?? '',
            $cover,
            (int)($_POST['related_company_id'] ?? 0) ?: null,
            isset($_POST['is_published']) ? 1 : 0,
            isset($_POST['is_published']) ? date('Y-m-d H:i:s') : null,
        ];

        if ($title_ar === '' || $title_en === '') $errors[] = t('form.error');

        if (!$errors) {
            if ($edit_id) {
                $pdo->prepare("UPDATE articles SET type=?, slug=?, title_ar=?, title_en=?, excerpt_ar=?, excerpt_en=?,
                               content_ar=?, content_en=?, cover_image=?, related_company_id=?, is_published=?,
                               published_at = COALESCE(published_at, ?) WHERE id=?")->execute([...$data, $edit_id]);
            } else {
                $pdo->prepare("INSERT INTO articles (type, slug, title_ar, title_en, excerpt_ar, excerpt_en,
                               content_ar, content_en, cover_image, related_company_id, is_published, published_at)
                               VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")->execute($data);
            }
            flash_set('success', t('admin.saved'));
            redirect('articles.php');
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'delete') {
    if (csrf_verify()) {
        $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([(int)$_POST['id']]);
        flash_set('success', t('admin.deleted'));
    }
    redirect('articles.php');
}

$editing = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $editing = $stmt->fetch();
}

$companies = $pdo->query("SELECT id, name FROM companies ORDER BY sort_order")->fetchAll();
$articles = $pdo->query("SELECT a.*, co.name AS company_name FROM articles a LEFT JOIN companies co ON co.id=a.related_company_id ORDER BY a.created_at DESC")->fetchAll();

$page_title = t('admin.articles');
require __DIR__ . '/includes/layout_start.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
  <div class="panel">
    <h3 style="margin-top:0"><?= $editing ? t('admin.edit') : t('admin.add') ?> — <?= t('admin.articles') ?></h3>
    <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="do" value="save">
      <input type="hidden" name="id" value="<?= (int)($editing['id'] ?? 0) ?>">
      <div class="form-grid">
        <div class="field">
          <label>النوع</label>
          <select name="type">
            <option value="blog" <?= ($editing['type'] ?? 'blog') === 'blog' ? 'selected' : '' ?>>مقال / Blog</option>
            <option value="guide" <?= ($editing['type'] ?? '') === 'guide' ? 'selected' : '' ?>>دليل / Guide</option>
          </select>
        </div>
        <div class="field"><label>Slug (اختياري)</label><input type="text" name="slug" value="<?= e($editing['slug'] ?? '') ?>"></div>
      </div>
      <div class="form-grid">
        <div class="field"><label>العنوان (عربي) *</label><input type="text" name="title_ar" required value="<?= e($editing['title_ar'] ?? '') ?>"></div>
        <div class="field"><label>Title (English) *</label><input type="text" name="title_en" required value="<?= e($editing['title_en'] ?? '') ?>"></div>
      </div>
      <div class="form-grid">
        <div class="field"><label>مقتطف (عربي)</label><input type="text" name="excerpt_ar" value="<?= e($editing['excerpt_ar'] ?? '') ?>"></div>
        <div class="field"><label>Excerpt (English)</label><input type="text" name="excerpt_en" value="<?= e($editing['excerpt_en'] ?? '') ?>"></div>
      </div>
      <div class="form-grid">
        <div class="field"><label>المحتوى (عربي) — HTML مسموح</label><textarea name="content_ar" rows="8"><?= e($editing['content_ar'] ?? '') ?></textarea></div>
        <div class="field"><label>Content (English) — HTML allowed</label><textarea name="content_en" rows="8"><?= e($editing['content_en'] ?? '') ?></textarea></div>
      </div>
      <div class="form-grid">
        <div class="field">
          <label>مرتبط بشركة (اختياري)</label>
          <select name="related_company_id">
            <option value="">—</option>
            <?php foreach ($companies as $co): ?>
              <option value="<?= (int)$co['id'] ?>" <?= (int)($editing['related_company_id'] ?? 0) === (int)$co['id'] ? 'selected' : '' ?>><?= e($co['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>صورة الغلاف</label>
          <input type="file" name="cover_image" accept="image/*">
          <?php if (!empty($editing['cover_image'])): ?><small>الحالية: <?= e($editing['cover_image']) ?></small><?php endif; ?>
        </div>
      </div>
      <label class="checkbox-row"><input type="checkbox" name="is_published" <?= !empty($editing['is_published']) ? 'checked' : '' ?>> منشور</label>
      <div style="margin-top:18px;display:flex;gap:10px">
        <button type="submit" class="btn btn-primary"><?= t('admin.save') ?></button>
        <a href="articles.php" class="btn btn-ghost">إلغاء</a>
      </div>
    </form>
  </div>
<?php else: ?>
  <div class="toolbar"><div></div><a href="articles.php?action=add" class="btn btn-primary"><?= t('admin.add') ?></a></div>
  <table class="admin-table">
    <thead><tr><th>العنوان</th><th>النوع</th><th>الشركة</th><th>منشور</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($articles as $a): ?>
        <tr>
          <td><?= e($a['title_ar']) ?></td>
          <td><?= e($a['type']) ?></td>
          <td><?= e($a['company_name'] ?? '-') ?></td>
          <td><?= $a['is_published'] ? '✔' : '—' ?></td>
          <td class="row-actions">
            <a href="articles.php?action=edit&id=<?= (int)$a['id'] ?>" class="btn btn-ghost btn-sm"><?= t('admin.edit') ?></a>
            <form method="post" onsubmit="return confirm('<?= t('admin.confirm_delete') ?>')" style="display:inline">
              <?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
              <button class="btn btn-danger btn-sm"><?= t('admin.delete') ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout_end.php'; ?>
