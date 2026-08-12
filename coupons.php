<?php
require_once __DIR__ . '/includes/bootstrap.php';
$coupons = $pdo->query("SELECT c.*, co.name AS company_name, co.slug AS company_slug
                         FROM coupons c JOIN companies co ON co.id = c.company_id
                         WHERE c.is_active = 1 AND (c.expires_at IS NULL OR c.expires_at >= CURDATE())
                         ORDER BY c.created_at DESC")->fetchAll();
$page_title = t('coupons.title');
require __DIR__ . '/includes/header.php';
?>
<section>
  <div class="container">
    <div class="section-head"><h2><?= t('coupons.title') ?></h2></div>
    <?php if (!$coupons): ?>
      <p class="empty-state"><?= t('coupons.none') ?></p>
    <?php else: ?>
      <div class="grid" style="gap:16px">
        <?php foreach ($coupons as $cp): ?>
          <div class="coupon-card">
            <div class="info">
              <span class="discount-tag"><?= e($cp['discount_text']) ?></span>
              <h4><?= e(field($cp,'title')) ?></h4>
              <p><?= e($cp['company_name']) ?> &middot; <?= e(field($cp,'description')) ?>
                <?php if ($cp['expires_at']): ?> &middot; <?= t('coupons.expires') ?> <?= e($cp['expires_at']) ?><?php endif; ?>
              </p>
            </div>
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
              <button class="coupon-code" data-copy="<?= e($cp['code']) ?>" data-copied-label="<?= t('coupons.copied') ?>" style="border:1px dashed rgba(255,255,255,.5);background:rgba(255,255,255,.12);color:#fff;cursor:pointer"><?= e($cp['code']) ?></button>
              <a href="go.php?company=<?= e($cp['company_slug']) ?>&coupon=<?= (int)$cp['id'] ?>" class="btn btn-accent btn-sm" target="_blank" rel="nofollow noopener"><?= t('coupons.use_now') ?></a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
