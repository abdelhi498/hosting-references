<?php
require_once __DIR__ . '/includes/bootstrap.php';

$featured = $pdo->query("SELECT * FROM companies WHERE is_active=1 AND is_featured=1 ORDER BY sort_order LIMIT 3")->fetchAll();
if (count($featured) < 3) {
    $featured = $pdo->query("SELECT * FROM companies WHERE is_active=1 ORDER BY sort_order LIMIT 3")->fetchAll();
}
$coupons = $pdo->query("SELECT c.*, co.name AS company_name, co.slug AS company_slug FROM coupons c
                         JOIN companies co ON co.id = c.company_id
                         WHERE c.is_active=1 ORDER BY c.created_at DESC LIMIT 3")->fetchAll();
$articles = $pdo->query("SELECT * FROM articles WHERE is_published=1 ORDER BY published_at DESC LIMIT 3")->fetchAll();

$page_title = t('home.hero_title');
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="container hero-grid">
    <div>
      <span class="eyebrow" style="color:#7dd8b8"><?= e(setting($pdo,'site_name_'.current_lang())) ?></span>
      <h1><?= t('home.hero_title') ?></h1>
      <p><?= t('home.hero_sub') ?></p>
      <div class="actions">
        <a href="compare.php" class="btn btn-primary"><?= t('home.cta_compare') ?></a>
        <a href="proxy-purchase.php" class="btn btn-outline"><?= t('home.cta_proxy') ?></a>
      </div>
      <div class="trust-strip">
        <div><b>5+</b><?= is_rtl() ? 'شركات موثوقة' : 'trusted providers' ?></div>
        <div><b>2026</b><?= is_rtl() ? 'مراجعات محدثة' : 'up-to-date reviews' ?></div>
        <div><b>AR/EN</b><?= is_rtl() ? 'محتوى بلغتين' : 'bilingual content' ?></div>
      </div>
    </div>
    <div class="hero-visual">
      <div class="hero-mock-card">
        <span class="hero-badge-float"><?= is_rtl() ? 'مقارنة فورية' : 'Live comparison' ?></span>
        <div class="mock-head">
          <span><?= is_rtl() ? 'أعلى تقييمًا هذا الشهر' : 'Top rated this month' ?></span>
        </div>
        <?php foreach ($featured as $mc): ?>
          <div class="mock-row">
            <div class="mname">
              <span class="mlogo"><?= e(strtoupper(substr($mc['name'],0,2))) ?></span>
              <?= e($mc['name']) ?>
            </div>
            <div class="mscore">&#9733; <?= e($mc['trust_score']) ?>/10</div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="section-head">
      <h2><?= t('home.featured') ?></h2>
      <a href="reviews.php" class="btn btn-ghost btn-sm"><?= t('home.view_all') ?></a>
    </div>
    <div class="grid grid-3">
      <?php foreach ($featured as $c): ?>
        <div class="card company-card">
          <?php if ($c['is_featured']): ?><span class="featured-badge"><?= is_rtl() ? 'الأعلى تقييمًا' : 'Top rated' ?></span><?php endif; ?>
          <div class="top">
            <div class="logo-badge"><?= e(strtoupper(substr($c['name'],0,2))) ?></div>
            <h3><?= e($c['name']) ?></h3>
            <div class="score-pill"><span>&#9733;</span> <?= e($c['trust_score']) ?>/10</div>
          </div>
          <p class="summary"><?= e(field($c,'summary')) ?></p>
          <div class="price"><?= t('company.starting_at') ?> <span class="ltr-value"><?= e($c['starting_price']) ?></span></div>
          <div class="actions">
            <a href="review.php?slug=<?= e($c['slug']) ?>" class="btn btn-ghost btn-sm"><?= t('company.read_review') ?></a>
            <a href="go.php?company=<?= e($c['slug']) ?>" class="btn btn-primary btn-sm" target="_blank" rel="nofollow noopener"><?= t('company.get_hosting') ?></a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section style="background:var(--navy-100)">
  <div class="container">
    <div class="section-head">
      <h2><?= t('home.latest_coupons') ?></h2>
      <a href="coupons.php" class="btn btn-ghost btn-sm"><?= t('home.view_all') ?></a>
    </div>
    <?php if (!$coupons): ?>
      <p class="empty-state"><?= t('coupons.none') ?></p>
    <?php else: ?>
      <div class="grid" style="gap:16px">
        <?php foreach ($coupons as $cp): ?>
          <div class="coupon-card">
            <div class="info">
              <span class="discount-tag"><?= e($cp['discount_text']) ?></span>
              <h4><?= e(field($cp,'title')) ?></h4>
              <p><?= e($cp['company_name']) ?> &middot; <?= e(field($cp,'description')) ?></p>
            </div>
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
              <button type="button" class="coupon-cta"
                      data-copy="<?= e($cp['code']) ?>"
                      data-goto="go.php?company=<?= e($cp['company_slug']) ?>&coupon=<?= (int)$cp['id'] ?>"
                      data-copied-label="<?= e(t('coupons.copied')) ?>">
                <span class="code"><?= e($cp['code']) ?></span>
                <span class="cta-label"><?= t('coupons.use_now') ?></span>
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section>
  <div class="container">
    <div class="section-head">
      <h2><?= t('home.latest_articles') ?></h2>
      <a href="blog.php" class="btn btn-ghost btn-sm"><?= t('home.view_all') ?></a>
    </div>
    <?php if (!$articles): ?>
      <p class="empty-state"><?= t('blog.none') ?></p>
    <?php else: ?>
      <div class="grid grid-3">
        <?php foreach ($articles as $a): ?>
          <a href="post.php?slug=<?= e($a['slug']) ?>" class="card article-card">
            <img src="<?= $a['cover_image'] ? 'uploads/'.e($a['cover_image']) : 'assets/img/placeholder.svg' ?>" alt="">
            <div class="body">
              <h3><?= e(field($a,'title')) ?></h3>
              <p><?= e(field($a,'excerpt')) ?></p>
              <span class="btn btn-ghost btn-sm"><?= t('blog.read_more') ?></span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
