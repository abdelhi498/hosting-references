<?php
/**
 * Shared header. Expects includes/config.php + includes/functions.php
 * already loaded, and an optional $page_title / $meta_description.
 */
$lang = current_lang();
$dir  = is_rtl() ? 'rtl' : 'ltr';
$site_name = setting($pdo, 'site_name_' . $lang, $lang === 'ar' ? 'مراجع الاستضافة' : 'Hosting References');
$meta_desc = $meta_description ?? setting($pdo, 'meta_description_' . $lang);
$current_page = basename($_SERVER['SCRIPT_NAME']);
?><!DOCTYPE html>
<html lang="<?= e($lang) ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title ?? $site_name) ?> | <?= e($site_name) ?></title>
<meta name="description" content="<?= e($meta_desc) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="lang-<?= e($lang) ?>">

<div class="topbar">
  <div class="container">
    <div class="social-links">
      <?php $wa = setting($pdo, 'social_whatsapp'); ?>
      <?php if ($wa): ?><a href="<?= e($wa) ?>" target="_blank" rel="noopener">WhatsApp</a><?php endif; ?>
    </div>
    <div class="lang-switch">
      <a href="<?= e(lang_url('ar')) ?>" class="<?= $lang === 'ar' ? 'active' : '' ?>">AR</a>
      <a href="<?= e(lang_url('en')) ?>" class="<?= $lang === 'en' ? 'active' : '' ?>">EN</a>
    </div>
  </div>
</div>

<header class="site-header">
  <div class="container nav-row">
    <a href="index.php" class="brand">
      <span class="mark">HR</span>
      <span><?= e($site_name) ?></span>
    </a>
    <nav class="main-nav">
      <a href="index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>"><?= t('nav.home') ?></a>
      <a href="reviews.php" class="<?= $current_page === 'reviews.php' || $current_page === 'review.php' ? 'active' : '' ?>"><?= t('nav.reviews') ?></a>
      <a href="compare.php" class="<?= $current_page === 'compare.php' ? 'active' : '' ?>"><?= t('nav.compare') ?></a>
      <a href="coupons.php" class="<?= $current_page === 'coupons.php' ? 'active' : '' ?>"><?= t('nav.coupons') ?></a>
      <a href="blog.php" class="<?= in_array($current_page, ['blog.php','post.php']) ? 'active' : '' ?>"><?= t('nav.blog') ?></a>
      <a href="proxy-purchase.php" class="<?= $current_page === 'proxy-purchase.php' ? 'active' : '' ?>"><?= t('nav.proxy') ?></a>
    </nav>
    <div class="header-cta">
      <a href="contact.php" class="btn btn-outline btn-sm"><?= t('nav.contact') ?></a>
      <a href="proxy-purchase.php" class="btn btn-accent btn-sm"><?= t('home.cta_proxy') ?></a>
    </div>
    <button class="burger" aria-label="menu">&#9776;</button>
  </div>
</header>

<?php foreach (flash_get() as $f): ?>
  <div class="container" style="margin-top:18px">
    <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
  </div>
<?php endforeach; ?>
