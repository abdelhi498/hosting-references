<?php
require_once __DIR__ . '/includes/bootstrap.php';

$errors = [];
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = t('form.error');
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        if ($name === '') $errors[] = t('contact.name') . ': ' . t('form.required');
        if ($email === '') $errors[] = t('contact.email') . ': ' . t('form.required');
        if ($message === '') $errors[] = t('contact.message') . ': ' . t('form.required');

        if (!$errors) {
            $stmt = $pdo->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject ?: null, $message]);
            $success = true;
        }
    }
}

$page_title = t('contact.title');
require __DIR__ . '/includes/header.php';
?>
<section>
  <div class="container">
    <div class="card" style="max-width:600px;margin:0 auto">
      <div class="section-head"><h2><?= t('contact.title') ?></h2></div>
      <?php if ($success): ?>
        <div class="alert alert-success"><?= t('contact.success') ?></div>
      <?php else: ?>
        <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
        <form method="post">
          <?= csrf_field() ?>
          <div class="field"><label><?= t('contact.name') ?> *</label><input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>"></div>
          <div class="field"><label><?= t('contact.email') ?> *</label><input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></div>
          <div class="field"><label><?= t('contact.subject') ?></label><input type="text" name="subject" value="<?= e($_POST['subject'] ?? '') ?>"></div>
          <div class="field"><label><?= t('contact.message') ?> *</label><textarea name="message" required><?= e($_POST['message'] ?? '') ?></textarea></div>
          <button type="submit" class="btn btn-primary btn-block"><?= t('contact.submit') ?></button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
