<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'mark_read') {
    if (csrf_verify()) {
        $pdo->prepare("UPDATE messages SET is_read=1 WHERE id=?")->execute([(int)$_POST['id']]);
    }
    redirect('messages.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'delete') {
    if (csrf_verify()) {
        $pdo->prepare("DELETE FROM messages WHERE id = ?")->execute([(int)$_POST['id']]);
        flash_set('success', t('admin.deleted'));
    }
    redirect('messages.php');
}

$messages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll();

$page_title = t('admin.messages');
require __DIR__ . '/includes/layout_start.php';
?>

<div class="grid" style="gap:14px">
  <?php foreach ($messages as $m): ?>
    <div class="panel" style="<?= $m['is_read'] ? 'opacity:.7' : 'border-inline-start:4px solid var(--emerald)' ?>">
      <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px">
        <div>
          <strong><?= e($m['name']) ?></strong> &lt;<?= e($m['email']) ?>&gt;
          <?php if ($m['subject']): ?> — <?= e($m['subject']) ?><?php endif; ?>
          <div style="font-size:12.5px;color:var(--muted)"><?= e($m['created_at']) ?></div>
        </div>
        <div class="row-actions">
          <?php if (!$m['is_read']): ?>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="mark_read"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="btn btn-ghost btn-sm">تحديد كمقروء</button></form>
          <?php endif; ?>
          <form method="post" onsubmit="return confirm('<?= t('admin.confirm_delete') ?>')"><?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="btn btn-danger btn-sm"><?= t('admin.delete') ?></button></form>
        </div>
      </div>
      <p style="margin-bottom:0;margin-top:10px"><?= nl2br(e($m['message'])) ?></p>
    </div>
  <?php endforeach; ?>
  <?php if (!$messages): ?><p class="empty-state">لا توجد رسائل بعد</p><?php endif; ?>
</div>

<?php require __DIR__ . '/includes/layout_end.php'; ?>
