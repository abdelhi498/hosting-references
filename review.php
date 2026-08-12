<?php
require_once __DIR__ . '/includes/bootstrap.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM companies WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$c = $stmt->fetch();
if (!$c) { http_response_code(404); require __DIR__.'/includes/header.php'; echo '<div class="container" style="padding:60px 0">'.(is_rtl()?'الشركة غير موجودة':'Company not found').'</div>'; require __DIR__.'/includes/footer.php'; exit; }

$fstmt = $pdo->prepare("SELECT fk.label_ar, fk.label_en, cf.value_ar, cf.value_en, cf.is_included
                         FROM feature_keys fk
                         LEFT JOIN company_features cf ON cf.feature_key_id = fk.id AND cf.company_id = ?
                         ORDER BY fk.sort_order");
$fstmt->execute([$c['id']]);
$features = $fstmt->fetchAll();

$cpstmt = $pdo->prepare("SELECT * FROM coupons WHERE company_id = ? AND is_active = 1 ORDER BY created_at DESC");
$cpstmt->execute([$c['id']]);
$coupons = $cpstmt->fetchAll();

$page_title = $c['name'] . ' - ' . t('company.read_review');
require __DIR__ . '/includes/header.php';
?>
<section style="padding-top:44px">
  <div class="container">
    <div class="card" style="max-width:900px;margin:0 auto">
      <div class="top" style="margin-bottom:18px">
        <div class="logo-badge" style="width:64px;height:64px;font-size:20px"><?= e(strtoupper(substr($c['name'],0,2))) ?></div>
        <div>
          <h1 style="margin:0 0 4px;font-size:26px"><?= e($c['name']) ?></h1>
          <a href="<?= e($c['website_url']) ?>" target="_blank" rel="nofollow noopener" style="font-size:13.5px;color:var(--muted)"><?= e($c['website_url']) ?></a>
        </div>
        <div class="score-pill" style="margin-inline-start:auto"><span>&#9733;</span> <?= e($c['trust_score']) ?>/10</div>
      </div>

      <p style="font-size:16px"><?= e(field($c,'summary')) ?></p>

      <div class="pc-grid">
        <div>
          <h4><?= t('company.pros') ?></h4>
          <ul class="pc-pros">
            <?php foreach (json_list($c['pros_'.current_lang()]) as $p): ?><li><?= e($p) ?></li><?php endforeach; ?>
          </ul>
        </div>
        <div>
          <h4><?= t('company.cons') ?></h4>
          <ul class="pc-cons">
            <?php foreach (json_list($c['cons_'.current_lang()]) as $cn): ?><li><?= e($cn) ?></li><?php endforeach; ?>
          </ul>
        </div>
      </div>

      <div class="table-wrap" style="margin:26px 0">
        <table class="compare">
          <thead><tr><th><?= t('compare.feature') ?></th><th><?= e($c['name']) ?></th></tr></thead>
          <tbody>
            <?php foreach ($features as $f): ?>
              <tr>
                <td><?= e(is_rtl() ? $f['label_ar'] : $f['label_en']) ?></td>
                <td>
                  <?php $val = is_rtl() ? $f['value_ar'] : $f['value_en']; ?>
                  <?= $val ? e($val) : ($f['is_included'] ? '<span class="yes">&#10003;</span>' : '<span class="no">&#10007;</span>') ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if ($coupons): ?>
        <h4 style="margin-bottom:12px"><?= t('coupons.title') ?></h4>
        <?php foreach ($coupons as $cp): ?>
          <div class="coupon-card" style="margin-bottom:12px">
            <div class="info">
              <span class="discount-tag"><?= e($cp['discount_text']) ?></span>
              <h4><?= e(field($cp,'title')) ?></h4>
            </div>
            <div style="display:flex;align-items:center;gap:12px">
              <span class="coupon-code"><?= e($cp['code']) ?></span>
              <a href="go.php?company=<?= e($c['slug']) ?>&coupon=<?= (int)$cp['id'] ?>" class="btn btn-accent btn-sm" target="_blank" rel="nofollow noopener"><?= t('coupons.use_now') ?></a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <div class="actions" style="margin-top:24px">
        <div class="price" style="font-size:20px"><?= t('company.starting_at') ?> <?= e($c['starting_price']) ?></div>
        <a href="go.php?company=<?= e($c['slug']) ?>" class="btn btn-primary" target="_blank" rel="nofollow noopener"><?= t('company.get_hosting') ?></a>
        <a href="proxy-purchase.php?company=<?= e($c['slug']) ?>" class="btn btn-outline" style="border-color:var(--navy-700);color:var(--navy-900)"><?= t('nav.proxy') ?></a>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
