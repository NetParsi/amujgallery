<?php
defined('_JEXEC') or die;

$products = amuj_products();
$orders   = [
    ['no' => 'AM-140508-2731', 'date' => '۸ آبان ۱۴۰۵', 'status' => 'در حال پردازش', 'total' => 7765000, 'items' => [1, 4]],
    ['no' => 'AM-140421-1984', 'date' => '۲۱ مهر ۱۴۰۵', 'status' => 'ارسال شده',     'total' => 3080000, 'items' => [2, 3, 9]],
    ['no' => 'AM-140317-0442', 'date' => '۱۷ شهریور ۱۴۰۵', 'status' => 'تحویل شده', 'total' => 2290000, 'items' => [7]],
];
?>
<div class="az-account">
    <ul class="az-account-nav">
        <li><a class="is-active" href="#">سفارش‌های من</a></li>
        <li><a href="#">اطلاعات حساب</a></li>
        <li><a href="#">آدرس‌ها</a></li>
        <li><a href="#">علاقه‌مندی‌ها</a></li>
        <li><a href="#">مرجوعی‌ها</a></li>
        <li><a href="<?php echo amuj_url('login'); ?>">خروج</a></li>
    </ul>
    <div>
        <h1 class="az-title">سلام سارا، خوش آمدید</h1>
        <p class="az-muted uk-margin-medium-bottom">سفارش‌های اخیر شما</p>
        <?php foreach ($orders as $o) : ?>
            <div class="az-order">
                <div>
                    <div><strong><?php echo amuj_fa($o['no']); ?></strong> — <?php echo $o['date']; ?></div>
                    <div class="az-muted"><?php echo amuj_fa(count($o['items'])); ?> محصول | <?php echo amuj_price($o['total']); ?></div>
                    <div class="az-order-thumbs">
                        <?php foreach ($o['items'] as $id) : ?><img src="<?php echo amuj_img($products[$id]['img'], 200); ?>" alt=""><?php endforeach; ?>
                    </div>
                </div>
                <div class="uk-text-left">
                    <span class="az-status"><?php echo $o['status']; ?></span>
                    <div class="uk-margin-small-top"><a class="az-link az-small" href="#">جزئیات سفارش</a></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
