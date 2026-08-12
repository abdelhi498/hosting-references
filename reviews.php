<?php
require_once __DIR__ . '/includes/bootstrap.php';
$companies = $pdo->query("SELECT * FROM companies WHERE is_active=1 ORDER BY sort_order")->fetchAll();
$page_title = t('nav.reviews');
require __DIR__ . '/includes/header.php';
?>
<section>
  <div class="container">
    <div class="section-head"><h2><?= t('nav.reviews') ?></h2></div>
    <div class="grid grid-3">
      <?php foreach ($companies as $c): ?>
        <div class="card company-card">
          <?php if ($c['is_featured']): ?><span class="featured-badge"><?= is_rtl() ? 'الأعلى تقييمًا' : 'Top rated' ?></span><?php endif; ?>
          <div class="top">
            <div class="logo-badge"><?= e(strtoupper(substr($c['name'],0,2))) ?></div>
            <h3><?= e($c['name']) ?></h3>
            <div class="score-pill"><span>&#9733;</span> <?= e($c['trust_score']) ?>/10</div>
          </div>
          <p class="summary"><?= e(field($c,'summary')) ?></p>
          <div class="price"><?= t('company.starting_at') ?> <?= e($c['starting_price']) ?></div>
          <div class="actions">
            <a href="review.php?slug=<?= e($c['slug']) ?>" class="btn btn-ghost btn-sm"><?= t('company.read_review') ?></a>
            <a href="go.php?company=<?= e($c['slug']) ?>" class="btn btn-primary btn-sm" target="_blank" rel="nofollow noopener"><?= t('company.get_hosting') ?></a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
