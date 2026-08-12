<?php
require_once __DIR__ . '/includes/bootstrap.php';
$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM articles WHERE slug = ? AND is_published = 1");
$stmt->execute([$slug]);
$a = $stmt->fetch();
if (!$a) { http_response_code(404); require __DIR__.'/includes/header.php'; echo '<div class="container" style="padding:60px 0">'.(is_rtl()?'المقال غير موجود':'Article not found').'</div>'; require __DIR__.'/includes/footer.php'; exit; }

$page_title = field($a,'title');
require __DIR__ . '/includes/header.php';
?>
<section style="padding-top:44px">
  <div class="container">
    <article class="card" style="max-width:800px;margin:0 auto">
      <?php if ($a['cover_image']): ?>
        <img src="uploads/<?= e($a['cover_image']) ?>" style="border-radius:var(--radius);margin-bottom:20px;aspect-ratio:16/9;object-fit:cover">
      <?php endif; ?>
      <h1 style="font-size:28px"><?= e(field($a,'title')) ?></h1>
      <div style="color:var(--muted);font-size:14px;margin-bottom:20px"><?= e(date('Y-m-d', strtotime($a['published_at'] ?? $a['created_at']))) ?></div>
      <div style="font-size:16px;line-height:1.9"><?= $a['content_'.current_lang()] ?? '' ?></div>
    </article>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
