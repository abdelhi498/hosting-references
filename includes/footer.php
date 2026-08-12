<?php $lang = current_lang(); $site_name = setting($pdo, 'site_name_' . $lang); ?>
<footer class="site-footer">
  <div class="container">
    <div class="cols">
      <div>
        <h5><?= e($site_name) ?></h5>
        <p style="max-width:320px;font-size:14px"><?= t('site.tagline') ?></p>
      </div>
      <div>
        <h5><?= t('nav.reviews') ?></h5>
        <ul>
          <li><a href="reviews.php"><?= t('nav.reviews') ?></a></li>
          <li><a href="compare.php"><?= t('nav.compare') ?></a></li>
          <li><a href="coupons.php"><?= t('nav.coupons') ?></a></li>
        </ul>
      </div>
      <div>
        <h5><?= t('nav.proxy') ?></h5>
        <ul>
          <li><a href="proxy-purchase.php"><?= t('nav.proxy') ?></a></li>
          <li><a href="blog.php"><?= t('nav.blog') ?></a></li>
          <li><a href="contact.php"><?= t('nav.contact') ?></a></li>
        </ul>
      </div>
      <div>
        <h5>Social</h5>
        <ul>
          <?php $fb = setting($pdo, 'social_facebook'); $yt = setting($pdo, 'social_youtube'); $tg = setting($pdo, 'social_telegram'); ?>
          <?php if ($fb): ?><li><a href="<?= e($fb) ?>" target="_blank" rel="noopener">Facebook</a></li><?php endif; ?>
          <?php if ($yt): ?><li><a href="<?= e($yt) ?>" target="_blank" rel="noopener">YouTube</a></li><?php endif; ?>
          <?php if ($tg): ?><li><a href="<?= e($tg) ?>" target="_blank" rel="noopener">Telegram</a></li><?php endif; ?>
        </ul>
      </div>
    </div>
    <div class="disclosure"><?= t('footer.disclosure') ?></div>
    <div class="bottom-bar">
      <span>&copy; <?= date('Y') ?> <?= e($site_name) ?> &mdash; <?= t('footer.rights') ?></span>
      <a href="admin/login.php" style="opacity:.6"><?= t('nav.admin') ?></a>
    </div>
  </div>
</footer>
<script src="assets/js/main.js"></script>
</body>
</html>
