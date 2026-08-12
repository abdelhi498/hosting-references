<?php
require_once __DIR__ . '/includes/bootstrap.php';

$allCompanies = $pdo->query("SELECT * FROM companies WHERE is_active=1 ORDER BY sort_order")->fetchAll();
$featured = array_values(array_filter($allCompanies, fn($c) => (int)$c['is_featured'] === 1));
if (count($featured) < 3) {
    $featured = array_slice($allCompanies, 0, 3);
} else {
    $featured = array_slice($featured, 0, 3);
}
$coupons = $pdo->query("SELECT c.*, co.name AS company_name, co.slug AS company_slug FROM coupons c
                         JOIN companies co ON co.id = c.company_id
                         WHERE c.is_active=1 ORDER BY c.created_at DESC LIMIT 3")->fetchAll();
$articles = $pdo->query("SELECT * FROM articles WHERE is_published=1 ORDER BY published_at DESC LIMIT 3")->fetchAll();

$page_title = t('home.hero_title');
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="hero-orb hero-orb-1"></div>
  <div class="hero-orb hero-orb-2"></div>
  <div class="hero-grid-pattern"></div>
  <div class="container hero-grid">
    <div>
      <span class="hero-kicker">
        <span class="hero-kicker-dot"></span>
        <?= t('home.hero_kicker') ?>
      </span>
      <h1><?= t('home.hero_title') ?></h1>
      <p><?= t('home.hero_sub') ?></p>
      <div class="actions">
        <a href="compare.php" class="btn btn-primary"><?= t('home.cta_compare') ?></a>
        <a href="proxy-purchase.php" class="btn btn-outline"><?= t('home.cta_proxy') ?></a>
      </div>
      <div class="trust-strip">
        <div><b>5+</b><?= t('home.stat_providers') ?></div>
        <div><b>2026</b><?= t('home.stat_year') ?></div>
        <div><b>AR/EN</b><?= t('home.stat_lang') ?></div>
      </div>
    </div>
    <div class="hero-visual">
      <span class="hero-float-icon fi-1">&#9733;</span>
      <span class="hero-float-icon fi-2">&#128274;</span>
      <span class="hero-float-icon fi-3">&#9889;</span>
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

<?php if ($allCompanies): ?>
<section class="partners-strip">
  <div class="container">
    <div class="partners-head">
      <h2><?= t('home.partners_title') ?></h2>
      <p><?= t('home.partners_sub') ?></p>
    </div>
    <div class="partners-row">
      <?php foreach ($allCompanies as $pc): ?>
        <a href="go.php?company=<?= e($pc['slug']) ?>" class="partner-pill" target="_blank" rel="nofollow noopener">
          <span class="mlogo"><?= e(strtoupper(substr($pc['name'],0,2))) ?></span>
          <?= e($pc['name']) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="why-section">
  <div class="container">
    <div class="section-head section-head-center">
      <h2><?= t('home.why_title') ?></h2>
      <p class="section-sub"><?= t('home.why_sub') ?></p>
    </div>
    <div class="grid why-grid">
      <div class="why-card">
        <div class="why-icon">&#9989;</div>
        <h3><?= t('home.why_1_title') ?></h3>
        <p><?= t('home.why_1_desc') ?></p>
      </div>
      <div class="why-card">
        <div class="why-icon">&#127991;&#65039;</div>
        <h3><?= t('home.why_2_title') ?></h3>
        <p><?= t('home.why_2_desc') ?></p>
      </div>
      <div class="why-card">
        <div class="why-icon">&#129309;</div>
        <h3><?= t('home.why_3_title') ?></h3>
        <p><?= t('home.why_3_desc') ?></p>
      </div>
      <div class="why-card">
        <div class="why-icon">&#127760;</div>
        <h3><?= t('home.why_4_title') ?></h3>
        <p><?= t('home.why_4_desc') ?></p>
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

<section class="how-section" style="background:var(--navy-100)">
  <div class="container">
    <div class="section-head section-head-center">
      <h2><?= t('home.how_title') ?></h2>
      <p class="section-sub"><?= t('home.how_sub') ?></p>
    </div>
    <div class="how-steps">
      <div class="how-step">
        <span class="how-num">1</span>
        <h3><?= t('home.how_1_title') ?></h3>
        <p><?= t('home.how_1_desc') ?></p>
      </div>
      <div class="how-step">
        <span class="how-num">2</span>
        <h3><?= t('home.how_2_title') ?></h3>
        <p><?= t('home.how_2_desc') ?></p>
      </div>
      <div class="how-step">
        <span class="how-num">3</span>
        <h3><?= t('home.how_3_title') ?></h3>
        <p><?= t('home.how_3_desc') ?></p>
      </div>
    </div>
  </div>
</section>

<section>
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

<section class="cta-banner-section">
  <div class="cta-banner-orb"></div>
  <div class="container cta-banner">
    <div>
      <h2><?= t('home.cta_banner_title') ?></h2>
      <p><?= t('home.cta_banner_sub') ?></p>
    </div>
    <div class="actions">
      <a href="compare.php" class="btn btn-primary"><?= t('home.cta_compare') ?></a>
      <a href="proxy-purchase.php" class="btn btn-outline"><?= t('home.cta_proxy') ?></a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
