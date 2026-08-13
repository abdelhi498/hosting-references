<?php
/**
 * Admin layout — opening half. Expects $page_title and admin session.
 * Usage: require this, print content, then require layout_end.php
 */
require_admin();
$current = basename($_SERVER['SCRIPT_NAME']);
$nav_items = [
    ['dashboard.php', 'admin.dashboard', ['super_admin','editor']],
    ['companies.php', 'admin.companies', ['super_admin','editor']],
    ['features.php', 'admin.features', ['super_admin','editor']],
    ['coupons.php', 'admin.coupons', ['super_admin','editor']],
    ['articles.php', 'admin.articles', ['super_admin','editor']],
    ['popup.php', 'popup.title', ['super_admin','editor']],
    ['proxy_requests.php', 'admin.proxy_requests', ['super_admin','editor']],
    ['customers.php', 'admin.customers', ['super_admin','editor']],
    ['messages.php', 'admin.messages', ['super_admin','editor']],
    ['settings.php', 'admin.settings', ['super_admin']],
    ['admins.php', 'admin.admins', ['super_admin']],
];
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title ?? '') ?> - <?= t('nav.admin') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="lang-ar admin-body">
<div class="admin-shell">
  <aside class="admin-sidebar">
    <div class="admin-brand"><span class="mark">HR</span> <?= t('nav.admin') ?></div>
    <nav>
      <?php foreach ($nav_items as [$href, $label, $roles]): ?>
        <?php if (in_array($_SESSION['admin_role'] ?? '', $roles, true)): ?>
          <a href="<?= e($href) ?>" class="<?= $current === $href ? 'active' : '' ?>"><?= t($label) ?></a>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>
    <div class="admin-user">
      <div><?= e($_SESSION['admin_name'] ?? '') ?></div>
      <a href="profile.php" class="btn btn-ghost btn-sm" style="margin-top:8px"><?= t('admin.change_password') ?></a>
      <a href="logout.php" class="btn btn-outline btn-sm" style="margin-top:8px"><?= t('admin.logout') ?></a>
    </div>
  </aside>
  <main class="admin-main">
    <div class="admin-topbar">
      <h1><?= e($page_title ?? '') ?></h1>
      <a href="../index.php" target="_blank" class="btn btn-ghost btn-sm"><?= is_rtl() ? 'عرض الموقع' : 'View site' ?></a>
    </div>
    <div class="admin-content">
      <?php foreach (flash_get() as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
      <?php endforeach; ?>
