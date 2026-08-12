<?php
require_once __DIR__ . '/includes/bootstrap.php';

$companies = $pdo->query("SELECT id, slug, name FROM companies WHERE is_active=1 ORDER BY sort_order")->fetchAll();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = t('form.error');
    } else {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $company_id = (int)($_POST['company_id'] ?? 0) ?: null;
        $plan = trim($_POST['plan'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if ($name === '') $errors[] = t('proxy.name') . ': ' . t('form.required');
        if ($phone === '') $errors[] = t('proxy.phone') . ': ' . t('form.required');

        if (!$errors) {
            $stmt = $pdo->prepare("INSERT INTO proxy_requests (name, phone, email, company_id, plan_desired, notes)
                                    VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $phone, $email ?: null, $company_id, $plan ?: null, $notes ?: null]);
            $success = true;
        }
    }
}

$preselect = $_GET['company'] ?? '';
$page_title = t('proxy.title');
require __DIR__ . '/includes/header.php';
?>
<section>
  <div class="container">
    <div class="grid grid-2" style="align-items:start">
      <div>
        <div class="section-head"><h2><?= t('proxy.title') ?></h2></div>
        <p style="font-size:16px;color:var(--muted)"><?= t('proxy.intro') ?></p>

        <div class="card" style="margin-top:24px">
          <h4 style="margin-top:0"><?= t('proxy.how_title') ?></h4>
          <ol style="padding-inline-start:20px;margin:0;line-height:2">
            <li><?= t('proxy.how_1') ?></li>
            <li><?= t('proxy.how_2') ?></li>
            <li><?= t('proxy.how_3') ?></li>
          </ol>
        </div>
      </div>

      <div class="card">
        <?php if ($success): ?>
          <div class="alert alert-success"><?= t('proxy.success') ?></div>
        <?php else: ?>
          <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
          <form method="post">
            <?= csrf_field() ?>
            <div class="field">
              <label><?= t('proxy.name') ?> *</label>
              <input type="text" name="name" required value="<?= e($_POST['name'] ?? '') ?>">
            </div>
            <div class="field">
              <label><?= t('proxy.phone') ?> *</label>
              <input type="text" name="phone" required value="<?= e($_POST['phone'] ?? '') ?>">
            </div>
            <div class="field">
              <label><?= t('proxy.email') ?></label>
              <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="field">
              <label><?= t('proxy.company') ?></label>
              <select name="company_id">
                <option value=""><?= t('form.select') ?></option>
                <?php foreach ($companies as $co): ?>
                  <option value="<?= (int)$co['id'] ?>" <?= $preselect === $co['slug'] ? 'selected' : '' ?>><?= e($co['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label><?= t('proxy.plan') ?></label>
              <input type="text" name="plan" value="<?= e($_POST['plan'] ?? '') ?>">
            </div>
            <div class="field">
              <label><?= t('proxy.notes') ?></label>
              <textarea name="notes"><?= e($_POST['notes'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block"><?= t('proxy.submit') ?></button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
