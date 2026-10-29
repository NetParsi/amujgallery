<?php
defined('_JEXEC') or die;

$products  = amuj_products();
$items     = [13 => ['size' => '۳۸', 'color' => 'مشکی', 'qty' => 1], 2 => ['size' => '۳۸', 'color' => 'کرم', 'qty' => 1]];
$total     = 0;
foreach ($items as $id => $item) {
    $total += $products[$id]['price'] * $item['qty'];
}
$free      = amuj_free_shipping();
$remaining = max(0, $free - $total);
$progress  = min(100, round($total / $free * 100));

// Empty cart state (demo: ?empty=1). Dynamic: HikaShop cart with no products.
if (isset($_GET['empty'])) : ?>
    <div class="az-thanks az-state">
        <div class="az-status-icon" aria-hidden="true">
            <svg viewBox="0 0 96 96">
                <circle class="az-status-ring" cx="48" cy="48" r="44"/>
                <path class="az-status-mark" pathLength="70" d="M32 40h32l-3 27H35z"/>
                <path class="az-status-mark az-status-mark--late" pathLength="70" d="M40 40v-5a8 8 0 0 1 16 0v5"/>
            </svg>
        </div>
        <h1>سبد خرید شما خالی است</h1>
        <p class="az-muted">کالاهایی که به سبد اضافه کنید اینجا نمایش داده می‌شوند. از جدیدترین‌های کالکشن دیدن کنید.</p>
        <div class="az-thanks-actions">
            <a class="az-btn" href="<?php echo amuj_url('category'); ?>">مشاهده محصولات</a>
            <a class="az-btn az-btn--ghost" href="<?php echo amuj_url('wishlist'); ?>">علاقه‌مندی‌های من</a>
        </div>
    </div>
    <div class="az-cart-more">
        <section class="az-section">
            <div class="az-section-head"><h2>جدیدترین‌ها</h2><a href="<?php echo amuj_url('category'); ?>">مشاهده همه</a></div>
            <div class="az-grid">
                <?php foreach (array_slice($products, 0, 4, true) as $id => $p) : amuj_card($id, $p); endforeach; ?>
            </div>
        </section>
    </div>
    <?php return;
endif;
?>
<?php // Same frame as checkout: centered, main column + 380px summary box ?>
<div class="az-checkout az-cart">
    <div class="az-cart-main">
        <div class="az-steps">
            <span class="is-active">۱. سبد خرید</span>
            <span>۲. ارسال</span>
            <span>۳. پرداخت</span>
        </div>

        <h1 class="az-title az-cart-title">سبد خرید <span class="az-muted">(<?php echo amuj_fa(count($items)); ?> کالا)</span></h1>

        <div class="az-cart-list">
            <?php foreach ($items as $id => $item) : $p = $products[$id]; ?>
                <div class="az-cart-row">
                    <a class="az-cart-row-img" href="<?php echo amuj_url('product', ['id' => $id]); ?>"><img src="<?php echo amuj_img($p['img'], 300); ?>" alt=""></a>
                    <div class="az-cart-row-info">
                        <a class="az-cart-row-name" href="<?php echo amuj_url('product', ['id' => $id]); ?>"><?php echo $p['name']; ?></a>
                        <span class="az-muted"><?php echo $item['color']; ?> | سایز <?php echo $item['size']; ?></span>
                        <div class="az-cart-row-actions">
                            <div class="az-qty"><button type="button" data-step="-">−</button><span data-n="<?php echo $item['qty']; ?>"><?php echo amuj_fa($item['qty']); ?></span><button type="button" data-step="+">+</button></div>
                            <a class="az-remove">حذف</a>
                        </div>
                    </div>
                    <strong class="az-cart-row-price"><?php echo amuj_price($p['price']); ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

        <aside class="az-summary az-cart-summary">
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

            <div class="az-summary-row">
                <span>هزینه ارسال</span>
                <span class="az-muted"><?php echo $remaining ? 'وابسته به آدرس' : 'رایگان'; ?></span>
            </div>

            <div class="az-summary-row is-total"><span>جمع کل</span><span><?php echo amuj_price($total); ?></span></div>

            <a class="az-btn az-btn--block az-cart-next" href="<?php echo amuj_url('checkout'); ?>">ادامه فرایند خرید</a>
            <p class="az-small az-muted uk-margin-small-top">هزینه ارسال نهایی پس از ثبت آدرس در مرحله بعد محاسبه می‌شود.</p>
        </aside>
</div>

<div class="az-cart-more">
    <section class="az-section">
        <div class="az-section-head"><h2>شاید بپسندید</h2></div>
        <div class="az-row-slider" uk-slider="finite: true">
            <ul class="uk-slider-items">
                <?php foreach (array_diff_key($products, $items) as $id => $p) : ?>
                    <li><?php amuj_card($id, $p); ?></li>
                <?php endforeach; ?>
            </ul><?php amuj_slider_nav(); ?>
        </div>
    </section>
</div>

<!-- Mobile: total and continue button stay pinned to the bottom -->
<div class="az-cart-bar">
    <div>
        <div class="az-total">جمع کل: <span><?php echo amuj_price($total); ?></span></div>
        <div class="az-small az-muted"><?php echo $remaining ? amuj_price($remaining) . ' تا ارسال رایگان' : 'ارسال رایگان'; ?></div>
    </div>
    <a class="az-btn" href="<?php echo amuj_url('checkout'); ?>">ادامه</a>
</div>
