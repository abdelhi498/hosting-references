<?php
require_once __DIR__ . '/includes/bootstrap.php';

$all_companies = $pdo->query("SELECT id, slug, name FROM companies WHERE is_active=1 ORDER BY sort_order")->fetchAll();

$selected_slugs = isset($_GET['c']) ? array_filter((array)$_GET['c']) : [];
$selected = [];
if ($selected_slugs) {
    $in = implode(',', array_fill(0, count($selected_slugs), '?'));
    $stmt = $pdo->prepare("SELECT * FROM companies WHERE is_active=1 AND slug IN ($in) ORDER BY sort_order");
    $stmt->execute(array_values($selected_slugs));
    $selected = $stmt->fetchAll();
}

$feature_keys = $pdo->query("SELECT * FROM feature_keys ORDER BY sort_order")->fetchAll();

$company_features = [];
if ($selected) {
    $ids = array_column($selected, 'id');
    $in2 = implode(',', array_fill(0, count($ids), '?'));
    $fstmt = $pdo->prepare("SELECT * FROM company_features WHERE company_id IN ($in2)");
    $fstmt->execute($ids);
    foreach ($fstmt->fetchAll() as $row) {
        $company_features[$row['company_id']][$row['feature_key_id']] = $row;
    }
}

$page_title = t('compare.title');
require __DIR__ . '/includes/header.php';
?>
<section>
  <div class="container">
    <div class="section-head"><h2><?= t('compare.title') ?></h2></div>

    <form method="get" class="card" style="margin-bottom:30px">
      <div class="field"><label><?= t('compare.pick') ?></label></div>
      <div class="form-grid">
        <?php foreach ($all_companies as $co): ?>
          <label class="checkbox-row">
            <input type="checkbox" name="c[]" value="<?= e($co['slug']) ?>" <?= in_array($co['slug'], $selected_slugs, true) ? 'checked' : '' ?>>
            <?= e($co['name']) ?>
          </label>
        <?php endforeach; ?>
      </div>
      <button type="submit" class="btn btn-primary" style="margin-top:16px"><?= t('compare.submit') ?></button>
    </form>

    <?php if ($selected): ?>
      <div class="table-wrap">
        <table class="compare">
          <thead>
            <tr>
              <th><?= t('compare.feature') ?></th>
              <?php foreach ($selected as $co): ?><th><?= e($co['name']) ?></th><?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><?= t('company.trust_score') ?></td>
              <?php foreach ($selected as $co): ?><td><strong><?= e($co['trust_score']) ?>/10</strong></td><?php endforeach; ?>
            </tr>
            <tr>
              <td><?= t('company.starting_at') ?></td>
              <?php foreach ($selected as $co): ?><td><span class="ltr-value"><?= e($co['starting_price']) ?></span></td><?php endforeach; ?>
            </tr>
            <?php foreach ($feature_keys as $fk): ?>
              <tr>
                <td><?= e(is_rtl() ? $fk['label_ar'] : $fk['label_en']) ?></td>
                <?php foreach ($selected as $co):
                  $cf = $company_features[$co['id']][$fk['id']] ?? null;
                  $val = $cf ? (is_rtl() ? $cf['value_ar'] : $cf['value_en']) : null;
                ?>
                  <td>
                    <?php if ($val): ?><span class="ltr-value"><?= e($val) ?></span>
                    <?php elseif ($cf && $cf['is_included']): ?><span class="yes">&#10003;</span>
                    <?php else: ?><span class="no">&#10007;</span>
                    <?php endif; ?>
                  </td>
                <?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
            <tr>
              <td></td>
              <?php foreach ($selected as $co): ?>
                <td><a href="go.php?company=<?= e($co['slug']) ?>" class="btn btn-primary btn-sm" target="_blank" rel="nofollow noopener"><?= t('company.get_hosting') ?></a></td>
              <?php endforeach; ?>
            </tr>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
