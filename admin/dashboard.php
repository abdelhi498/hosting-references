<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

$stats = [
    'companies' => $pdo->query("SELECT COUNT(*) FROM companies")->fetchColumn(),
    'coupons' => $pdo->query("SELECT COUNT(*) FROM coupons WHERE is_active=1")->fetchColumn(),
    'clicks_30d' => $pdo->query("SELECT COUNT(*) FROM clicks WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn(),
    'proxy_pending' => $pdo->query("SELECT COUNT(*) FROM proxy_requests WHERE status='pending'")->fetchColumn(),
];

$recent_clicks = $pdo->query("SELECT c.created_at, co.name FROM clicks c JOIN companies co ON co.id=c.company_id ORDER BY c.created_at DESC LIMIT 8")->fetchAll();
$recent_proxy = $pdo->query("SELECT pr.*, co.name AS company_name FROM proxy_requests pr LEFT JOIN companies co ON co.id = pr.company_id ORDER BY pr.created_at DESC LIMIT 6")->fetchAll();

$page_title = t('admin.dashboard');
require __DIR__ . '/includes/layout_start.php';
?>

<div class="stat-grid">
  <div class="stat-card"><div class="num"><?= (int)$stats['companies'] ?></div><div class="lbl"><?= t('admin.companies') ?></div></div>
  <div class="stat-card"><div class="num"><?= (int)$stats['coupons'] ?></div><div class="lbl"><?= t('admin.coupons') ?> (نشطة)</div></div>
  <div class="stat-card"><div class="num"><?= (int)$stats['clicks_30d'] ?></div><div class="lbl">نقرات الأفلييت (30 يوم)</div></div>
  <div class="stat-card"><div class="num"><?= (int)$stats['proxy_pending'] ?></div><div class="lbl">طلبات وكالة معلّقة</div></div>
</div>

<div class="grid grid-2" style="align-items:start">
  <div class="panel">
    <h3 style="margin-top:0">أحدث النقرات على روابط الأفلييت</h3>
    <?php if (!$recent_clicks): ?><p class="empty-state">لا توجد نقرات بعد</p><?php endif; ?>
    <table class="admin-table">
      <?php foreach ($recent_clicks as $rc): ?>
        <tr><td><?= e($rc['name']) ?></td><td><?= e($rc['created_at']) ?></td></tr>
      <?php endforeach; ?>
    </table>
  </div>
  <div class="panel">
    <h3 style="margin-top:0">أحدث طلبات الشراء بالوكالة</h3>
    <?php if (!$recent_proxy): ?><p class="empty-state">لا توجد طلبات بعد</p><?php endif; ?>
    <table class="admin-table">
      <?php foreach ($recent_proxy as $rp): ?>
        <tr>
          <td><?= e($rp['name']) ?></td>
          <td><?= e($rp['company_name'] ?? '-') ?></td>
          <td><span class="status-<?= e($rp['status']) ?>"><?= e($rp['status']) ?></span></td>
        </tr>
      <?php endforeach; ?>
    </table>
    <a href="proxy_requests.php" class="btn btn-ghost btn-sm" style="margin-top:14px">عرض كل الطلبات</a>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_end.php'; ?>
