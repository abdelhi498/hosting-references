<?php
require_once __DIR__ . '/includes/bootstrap.php';
$articles = $pdo->query("SELECT * FROM articles WHERE is_published = 1 ORDER BY published_at DESC")->fetchAll();
$page_title = t('blog.title');
require __DIR__ . '/includes/header.php';
?>
<section>
  <div class="container">
    <div class="section-head"><h2><?= t('blog.title') ?></h2></div>
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
