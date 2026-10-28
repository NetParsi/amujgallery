<?php
defined('_JEXEC') or die;

$products  = amuj_products();
$items     = [13 => ['size' => '۳۸', 'color' => 'مشکی', 'qty' => 1], 2 => ['size' => 'M', 'color' => 'مشکی', 'qty' => 1]];
$total     = 0;
foreach ($items as $id => $item) {
    $total += $products[$id]['price'] * $item['qty'];
}
$free      = amuj_free_shipping();
$remaining = max(0, $free - $total);
$progress  = min(100, round($total / $free * 100));
$rates     = amuj_shipping_rates();
?>
<div class="az-cart">
    <ul class="az-tabs">
        <li><a class="is-active" href="<?php echo amuj_url('cart'); ?>">سبد خرید (<?php echo amuj_fa(count($items)); ?>)</a></li>
        <li><a href="#">علاقه‌مندی‌ها</a></li>
    </ul>

    <div class="az-cart-layout">
        <div class="az-cart-items">
            <?php foreach ($items as $id => $item) : $p = $products[$id]; ?>
                <div class="az-cart-item">
                    <a href="<?php echo amuj_url('product', ['id' => $id]); ?>"><img src="<?php echo amuj_img($p['img'], 600); ?>" alt=""></a>
                    <div class="az-meta">
                        <div><?php echo $p['name']; ?></div>
                        <div><?php echo amuj_price($p['price']); ?></div>
                        <div class="az-muted"><?php echo $item['color']; ?> | سایز <?php echo $item['size']; ?></div>
                        <div class="az-qty"><button type="button" data-step="-">−</button><span data-n="<?php echo $item['qty']; ?>"><?php echo amuj_fa($item['qty']); ?></span><button type="button" data-step="+">+</button></div>
                        <a class="az-remove">حذف</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <aside class="az-summary az-cart-summary" data-az-cart data-subtotal="<?php echo $total; ?>" data-free="<?php echo $free; ?>">
            <h2 class="az-title">خلاصه سبد</h2>

            <div class="az-freeship<?php echo $remaining ? '' : ' is-done'; ?>">
                <p>
                    <?php if ($remaining) : ?>
                        <strong><?php echo amuj_price($remaining); ?></strong> تا ارسال رایگان باقی مانده
                    <?php else : ?>
                        ارسال این سفارش <strong>رایگان</strong> است
                    <?php endif; ?>
                </p>
                <div class="az-club-bar"><span style="--p: <?php echo $progress; ?>%"></span></div>
            </div>

            <div class="az-summary-row"><span>جمع کالاها (<?php echo amuj_fa(count($items)); ?>)</span><span><?php echo amuj_price($total); ?></span></div>

            <div class="az-field az-ship-field">
                <label for="az-ship-province">هزینه ارسال بر اساس استان</label>
                <select id="az-ship-province" class="az-select" data-az-ship>
                    <option value="">انتخاب استان</option>
                    <?php foreach ($rates as $province => [$method, $cost]) : ?>
                        <option value="<?php echo $cost; ?>" data-method="<?php echo $method; ?>"><?php echo $province; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="az-summary-row">
                <span>هزینه ارسال <small class="az-muted" data-az-ship-method></small></span>
                <span data-az-ship-cost><?php echo $remaining ? 'وابسته به آدرس' : 'رایگان'; ?></span>
            </div>

            <div class="az-summary-row is-total"><span>جمع کل</span><span data-az-cart-total><?php echo amuj_price($total); ?></span></div>

            <a class="az-btn az-btn--block az-cart-next" href="<?php echo amuj_url('checkout'); ?>">ادامه فرایند خرید</a>
            <p class="az-small az-muted uk-margin-small-top">هزینه ارسال نهایی پس از ثبت آدرس در مرحله بعد محاسبه می‌شود.</p>
        </aside>
    </div>

    <section class="az-section" style="padding-left:0;padding-right:0">
        <div class="az-section-head"><h2>شاید بپسندید</h2></div>
        <div class="az-row-slider" uk-slider="finite: true">
            <ul class="uk-slider-items">
                <?php foreach (array_slice($products, 4, null, true) as $id => $p) : ?>
                    <li><?php amuj_card($id, $p); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
</div>

<!-- Mobile: total and continue button stay pinned to the bottom -->
<div class="az-cart-bar">
    <div>
        <div class="az-total">جمع کل: <span data-az-cart-total><?php echo amuj_price($total); ?></span></div>
        <div class="az-small az-muted"><?php echo $remaining ? amuj_price($remaining) . ' تا ارسال رایگان' : 'ارسال رایگان'; ?></div>
    </div>
    <a class="az-btn" href="<?php echo amuj_url('checkout'); ?>">ادامه</a>
</div>
