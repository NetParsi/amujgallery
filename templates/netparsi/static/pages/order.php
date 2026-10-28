<?php
defined('_JEXEC') or die;

$products = amuj_products();
$orders   = amuj_orders();
$no       = isset($_GET['no'], $orders[$_GET['no']]) ? $_GET['no'] : array_key_first($orders);
$order    = $orders[$no];
[$label, $mod] = amuj_order_status($order);
$steps    = amuj_order_steps();
$subtotal = amuj_order_total($order) - $order['shipping'];
$address  = amuj_addresses()[0];

amuj_account_open('order');
?>
<a class="az-back" href="<?php echo amuj_url('orders'); ?>">→ بازگشت به سفارش‌ها</a>

<div class="az-order-head">
    <div>
        <h1 class="az-account-title" dir="ltr"><?php echo amuj_fa($no); ?></h1>
        <span class="az-muted">ثبت شده در <?php echo $order['date']; ?></span>
    </div>
    <span class="az-badge az-badge--<?php echo $mod; ?>"><?php echo $label; ?></span>
</div>

<?php if ($order['step'] >= 0) : ?>
    <ol class="az-track">
        <?php foreach ($steps as $i => $step) : ?>
            <li class="<?php echo $i < $order['step'] ? 'is-done' : ($i === $order['step'] ? 'is-current' : ''); ?>" style="--i: <?php echo $i; ?>">
                <span><?php echo $step; ?></span>
            </li>
        <?php endforeach; ?>
    </ol>
<?php else : ?>
    <div class="az-thanks-note">این سفارش لغو شده و مبلغ آن به کیف پول شما بازگردانده شده است.</div>
<?php endif; ?>

<?php if ($order['tracking']) : ?>
    <div class="az-tracking">
        <div>
            <span>کد رهگیری مرسوله (پست)</span>
            <strong dir="ltr" data-az-copy-text><?php echo $order['tracking']; ?></strong>
        </div>
        <button class="az-btn az-btn--ghost" type="button" data-az-copy>کپی کد</button>
    </div>
<?php endif; ?>

<div class="az-order-detail">
    <section>
        <h2 class="az-dash-h">اقلام سفارش</h2>
        <?php foreach ($order['items'] as $id => [$size, $color]) : $p = $products[$id]; ?>
            <div class="az-summary-item">
                <a href="<?php echo amuj_url('product', ['id' => $id]); ?>"><img src="<?php echo amuj_img($p['img'], 200); ?>" alt=""></a>
                <div>
                    <div><?php echo $p['name']; ?></div>
                    <div class="az-muted"><?php echo $color; ?> | سایز <?php echo $size; ?> | تعداد ۱</div>
                    <div><?php echo amuj_price($p['price']); ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <section>
        <h2 class="az-dash-h">ارسال و پرداخت</h2>
        <dl class="az-dl">
            <dt>تحویل‌گیرنده</dt><dd><?php echo $address['name']; ?> — <span dir="ltr"><?php echo $address['phone']; ?></span></dd>
            <dt>آدرس</dt><dd><?php echo $address['province'] . '، ' . $address['address']; ?></dd>
            <dt>روش ارسال</dt><dd><?php echo $order['method']; ?></dd>
            <dt>روش پرداخت</dt><dd><?php echo $order['pay']; ?></dd>
        </dl>
        <div class="az-order-sum">
            <div class="az-summary-row"><span>جمع کالاها</span><span><?php echo amuj_price($subtotal); ?></span></div>
            <div class="az-summary-row"><span>هزینه ارسال</span><span><?php echo $order['shipping'] ? amuj_price($order['shipping']) : 'رایگان'; ?></span></div>
            <div class="az-summary-row is-total"><span>مبلغ کل</span><span><?php echo amuj_price(amuj_order_total($order)); ?></span></div>
        </div>
    </section>
</div>

<div class="az-order-actions">
    <a class="az-btn az-btn--ghost" href="#" onclick="window.print(); return false;">چاپ فاکتور</a>
    <?php if ($order['step'] === 4) : ?><a class="az-btn az-btn--ghost" href="<?php echo amuj_url('help'); ?>">درخواست مرجوعی</a><?php endif; ?>
    <a class="az-btn az-btn--ghost" href="<?php echo amuj_url('help'); ?>#contact">پشتیبانی سفارش</a>
</div>
<?php amuj_account_close(); ?>
