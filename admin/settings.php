<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_role('super_admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'save') {
    if (csrf_verify()) {
        $fields = ['site_name_ar','site_name_en','meta_description_ar','meta_description_en',
                   'social_facebook','social_youtube','social_whatsapp','social_telegram'];
        foreach ($fields as $f) {
            $val = trim($_POST[$f] ?? '');
            $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                           ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)")->execute([$f, $val]);
        }

        // Optional logo upload
        if (!empty($_FILES['site_logo']['name'])) {
            $ext = strtolower(pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png','jpg','jpeg','svg','webp'], true)) {
                $filename = 'logo-' . time() . '.' . $ext;
                move_uploaded_file($_FILES['site_logo']['tmp_name'], UPLOADS_DIR . '/' . $filename);
                $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('site_logo', ?)
                               ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)")->execute([$filename]);
            }
        }
        flash_set('success', t('admin.saved'));
        redirect('settings.php');
    }
}

$s = get_settings($pdo);
$page_title = t('admin.settings');
require __DIR__ . '/includes/layout_start.php';
?>

<div class="panel">
  <form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="do" value="save">
    <div class="form-grid">
      <div class="field"><label>اسم الموقع (عربي)</label><input type="text" name="site_name_ar" value="<?= e($s['site_name_ar'] ?? '') ?>"></div>
      <div class="field"><label>Site name (English)</label><input type="text" name="site_name_en" value="<?= e($s['site_name_en'] ?? '') ?>"></div>
    </div>
    <div class="form-grid">
      <div class="field"><label>وصف Meta (عربي)</label><textarea name="meta_description_ar"><?= e($s['meta_description_ar'] ?? '') ?></textarea></div>
      <div class="field"><label>Meta description (English)</label><textarea name="meta_description_en"><?= e($s['meta_description_en'] ?? '') ?></textarea></div>
    </div>
    <div class="field"><label>الشعار (Logo)</label><input type="file" name="site_logo" accept="image/*"><?php if (!empty($s['site_logo'])): ?><small>الحالي: <?= e($s['site_logo']) ?></small><?php endif; ?></div>
    <div class="form-grid">
      <div class="field"><label>رابط Facebook</label><input type="text" name="social_facebook" value="<?= e($s['social_facebook'] ?? '') ?>"></div>
      <div class="field"><label>رابط YouTube</label><input type="text" name="social_youtube" value="<?= e($s['social_youtube'] ?? '') ?>"></div>
    </div>
    <div class="form-grid">
      <div class="field"><label>رابط WhatsApp</label><input type="text" name="social_whatsapp" value="<?= e($s['social_whatsapp'] ?? '') ?>"></div>
      <div class="field"><label>رابط Telegram</label><input type="text" name="social_telegram" value="<?= e($s['social_telegram'] ?? '') ?>"></div>
    </div>
    <button type="submit" class="btn btn-primary"><?= t('admin.save') ?></button>
  </form>
</div>

<?php require __DIR__ . '/includes/layout_end.php'; ?>
