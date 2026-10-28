<?php
defined('_JEXEC') or die;

/* 404 / error content. Used by error.php (real errors) and by ?page=notfound (preview). */
$code     = isset($amujErrorCode) ? (int) $amujErrorCode : 404;
$products = array_slice(amuj_products(), 0, 4, true);
?>
<div class="az-error">
    <div class="az-error-code" aria-hidden="true"><?php echo amuj_fa($code); ?></div>
    <h1><?php echo $code === 404 ? 'صفحه‌ای که دنبالش بودید پیدا نشد' : 'خطایی رخ داد'; ?></h1>
    <p class="az-muted"><?php echo $code === 404
        ? 'ممکن است آدرس اشتباه باشد یا این صفحه جابه‌جا شده باشد. از جستجو یا لینک‌های زیر استفاده کنید.'
        : 'لطفاً چند لحظه دیگر دوباره تلاش کنید. اگر مشکل ادامه داشت با پشتیبانی تماس بگیرید.'; ?></p>
    <div class="az-error-actions">
        <a class="az-btn" href="<?php echo amuj_url('home'); ?>">بازگشت به صفحه اصلی</a>
        <a class="az-btn az-btn--ghost" href="<?php echo amuj_url('search'); ?>" data-az-search>جستجو در فروشگاه</a>
    </div>
</div>

<section class="az-section">
    <div class="az-section-head"><h2>شاید این‌ها را بپسندید</h2><a href="<?php echo amuj_url('category'); ?>">مشاهده همه</a></div>
    <div class="az-grid">
        <?php foreach ($products as $id => $p) : ?>
            <?php amuj_card($id, $p); ?>
        <?php endforeach; ?>
    </div>
</section>
