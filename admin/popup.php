<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();
require_once __DIR__ . '/../includes/popup_render.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'save') {
    if (csrf_verify()) {
        $delay = max(0, min(120, (int)($_POST['popup_delay_seconds'] ?? 4)));
        $frequency = in_array($_POST['popup_frequency'] ?? '', ['every_visit', 'once_session', 'once_day'], true)
            ? $_POST['popup_frequency'] : 'once_session';

        $values = [
            'popup_enabled'        => isset($_POST['popup_enabled']) ? '1' : '0',
            'popup_coupon_id'      => (int)($_POST['popup_coupon_id'] ?? 0) ?: '',
            'popup_custom_link'    => trim($_POST['popup_custom_link'] ?? ''),
            'popup_badge_text'     => trim($_POST['popup_badge_text'] ?? ''),
            'popup_title_ar'       => trim($_POST['popup_title_ar'] ?? ''),
            'popup_title_en'       => trim($_POST['popup_title_en'] ?? ''),
            'popup_desc_ar'        => trim($_POST['popup_desc_ar'] ?? ''),
            'popup_desc_en'        => trim($_POST['popup_desc_en'] ?? ''),
            'popup_cta_ar'         => trim($_POST['popup_cta_ar'] ?? ''),
            'popup_cta_en'         => trim($_POST['popup_cta_en'] ?? ''),
            'popup_delay_seconds'  => (string)$delay,
            'popup_frequency'      => $frequency,
        ];
        foreach ($values as $key => $val) {
            $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                           ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)")->execute([$key, $val]);
        }
        flash_set('success', t('admin.saved'));
        redirect('popup.php');
    }
}

$s = get_settings($pdo);
$coupons = $pdo->query("SELECT c.id, c.code, c.discount_text, co.name AS company_name
                         FROM coupons c JOIN companies co ON co.id = c.company_id
                         WHERE c.is_active = 1 AND co.is_active = 1
                         ORDER BY co.name")->fetchAll();

$page_title = t('popup.title');
require __DIR__ . '/includes/layout_start.php';
?>

<div class="grid grid-2" style="align-items:start">
  <div class="panel">
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="do" value="save">

      <label class="checkbox-row" style="margin-bottom:18px">
        <input type="checkbox" name="popup_enabled" <?= !empty($s['popup_enabled']) ? 'checked' : '' ?>>
        <?= t('popup.enabled') ?>
      </label>

      <div class="field">
        <label><?= t('popup.linked_coupon') ?></label>
        <select name="popup_coupon_id">
          <option value=""><?= t('popup.no_coupon') ?></option>
          <?php foreach ($coupons as $cp): ?>
            <option value="<?= (int)$cp['id'] ?>" <?= (int)($s['popup_coupon_id'] ?? 0) === (int)$cp['id'] ? 'selected' : '' ?>>
              <?= e($cp['company_name']) ?> — <?= e($cp['code']) ?><?= $cp['discount_text'] ? ' (' . e($cp['discount_text']) . ')' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label><?= t('popup.custom_link') ?></label>
        <input type="text" name="popup_custom_link" value="<?= e($s['popup_custom_link'] ?? '') ?>" placeholder="https://example.com/special-offer">
      </div>
      <div class="field">
        <label><?= t('popup.badge_text') ?></label>
        <input type="text" name="popup_badge_text" value="<?= e($s['popup_badge_text'] ?? '') ?>" placeholder="70% OFF">
      </div>

      <div class="form-grid">
        <div class="field"><label><?= t('popup.title_ar') ?></label><input type="text" name="popup_title_ar" value="<?= e($s['popup_title_ar'] ?? '') ?>"></div>
        <div class="field"><label><?= t('popup.title_en') ?></label><input type="text" name="popup_title_en" value="<?= e($s['popup_title_en'] ?? '') ?>"></div>
      </div>
      <div class="form-grid">
        <div class="field"><label><?= t('popup.desc_ar') ?></label><textarea name="popup_desc_ar"><?= e($s['popup_desc_ar'] ?? '') ?></textarea></div>
        <div class="field"><label><?= t('popup.desc_en') ?></label><textarea name="popup_desc_en"><?= e($s['popup_desc_en'] ?? '') ?></textarea></div>
      </div>
      <div class="form-grid">
        <div class="field"><label><?= t('popup.cta_ar') ?></label><input type="text" name="popup_cta_ar" value="<?= e($s['popup_cta_ar'] ?? '') ?>" placeholder="<?= t('coupons.use_now') ?>"></div>
        <div class="field"><label><?= t('popup.cta_en') ?></label><input type="text" name="popup_cta_en" value="<?= e($s['popup_cta_en'] ?? '') ?>" placeholder="Use coupon"></div>
      </div>
      <div class="form-grid">
        <div class="field"><label><?= t('popup.delay') ?></label><input type="number" min="0" max="120" name="popup_delay_seconds" value="<?= e($s['popup_delay_seconds'] ?? '4') ?>"></div>
        <div class="field">
          <label><?= t('popup.frequency') ?></label>
          <?php $freq = $s['popup_frequency'] ?? 'once_session'; ?>
          <select name="popup_frequency">
            <option value="every_visit" <?= $freq === 'every_visit' ? 'selected' : '' ?>><?= t('popup.freq_every_visit') ?></option>
            <option value="once_session" <?= $freq === 'once_session' ? 'selected' : '' ?>><?= t('popup.freq_once_session') ?></option>
            <option value="once_day" <?= $freq === 'once_day' ? 'selected' : '' ?>><?= t('popup.freq_once_day') ?></option>
          </select>
        </div>
      </div>

      <button type="submit" class="btn btn-primary"><?= t('admin.save') ?></button>
    </form>
  </div>

  <div class="panel">
    <h3 style="margin-top:0"><?= t('popup.preview') ?></h3>
    <?php $preview = render_promo_popup($pdo, true); ?>
    <?php if ($preview): ?>
      <?= $preview ?>
    <?php else: ?>
      <p class="empty-state"><?= is_rtl() ? 'أضف عنوانًا وكوبونًا أو رابطًا مخصصًا لمعاينة البوب أب' : 'Add a title and either a coupon or a custom link to preview the popup' ?></p>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_end.php'; ?>
