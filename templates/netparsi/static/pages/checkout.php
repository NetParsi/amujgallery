<?php
defined('_JEXEC') or die;

$products = amuj_products();
$items    = [13 => '۳۸', 2 => 'M'];
$subtotal = $products[13]['price'] + $products[2]['price'];
?>
<div class="az-checkout">
    <div>
        <div class="az-steps">
            <span>۱. سبد خرید</span>
            <span class="is-active">۲. ارسال</span>
            <span>۳. پرداخت</span>
        </div>

        <h2 class="az-title">آدرس تحویل</h2>
        <form class="az-form-grid" onsubmit="return false">
            <div class="az-field"><label>نام</label><input class="az-input" value="سارا"></div>
            <div class="az-field"><label>نام خانوادگی</label><input class="az-input" value="محمدی"></div>
            <div class="az-field"><label>استان</label>
                <select class="az-select"><option>تهران</option><option>اصفهان</option><option>فارس</option><option>خراسان رضوی</option></select>
            </div>
            <div class="az-field"><label>شهر</label><input class="az-input" value="تهران"></div>
            <div class="az-field" style="grid-column:1/-1"><label>آدرس کامل</label><input class="az-input" placeholder="خیابان، کوچه، پلاک، واحد"></div>
            <div class="az-field"><label>کد پستی</label><input class="az-input" placeholder="۱۰ رقم"></div>
            <div class="az-field"><label>تلفن همراه</label><input class="az-input" placeholder="۰۹۱۲۰۰۰۰۰۰۰"></div>
        </form>

        <h2 class="az-title uk-margin-large-top">روش ارسال</h2>
        <label class="az-option"><span><input type="radio" name="ship" value="85000" data-label="پست پیشتاز" checked>پست پیشتاز — ۳ تا ۵ روز کاری</span><span><?php echo amuj_price(85000); ?></span></label>
        <label class="az-option"><span><input type="radio" name="ship" value="120000" data-label="پیک فوری تهران">پیک فوری (تهران) — همان روز</span><span><?php echo amuj_price(120000); ?></span></label>
        <label class="az-option"><span><input type="radio" name="ship" value="0" data-label="تحویل حضوری">تحویل حضوری از فروشگاه</span><span>رایگان</span></label>

        <h2 class="az-title uk-margin-large-top">روش پرداخت</h2>
        <label class="az-option"><span><input type="radio" name="pay" checked>درگاه پرداخت اینترنتی (کلیه کارت‌های عضو شتاب)</span></label>
        <label class="az-option"><span><input type="radio" name="pay">کارت به کارت</span></label>
    </div>

    <aside class="az-summary">
        <h2 class="az-title">خلاصه سفارش</h2>
        <?php foreach ($items as $id => $size) : $p = $products[$id]; ?>
            <div class="az-summary-item">
                <img src="<?php echo amuj_img($p['img'], 200); ?>" alt="">
                <div>
                    <div><?php echo $p['name']; ?></div>
                    <div class="az-muted">سایز <?php echo $size; ?> | تعداد ۱</div>
                    <div><?php echo amuj_price($p['price']); ?></div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php
        /*
         * Wallet + discount blocks. Sample: the wallet balance comes from the customer club, discount
         * code AMUJ10 = 10% off. Dynamic: HikaShop coupon field + the club extension.
         */
        $credit   = ($amujUser = amuj_current_user()) ? $amujUser['wallet'] : 1250000;
        $shipping = 85000; // the checked shipping method below
        ?>
        <div class="az-co-block">
            <label class="az-co-credit">
                <input type="checkbox" data-az-credit="<?php echo $credit; ?>">
                <span>
                    <strong>پرداخت از کیف پول</strong>
                    <small>موجودی کیف پول: <?php echo amuj_price($credit); ?></small>
                </span>
            </label>
        </div>

        <div class="az-co-block">
            <strong class="az-co-block-title">کد تخفیف</strong>
            <form class="az-co-coupon" data-az-coupon onsubmit="return false">
                <input class="az-input" name="coupon" placeholder="کد تخفیف را وارد کنید" autocomplete="off">
                <button class="az-btn az-btn--ghost" type="submit">اعمال</button>
            </form>
            <p class="az-co-msg" data-az-coupon-msg></p>
        </div>

        <div data-az-totals data-subtotal="<?php echo $subtotal; ?>" data-shipping="<?php echo $shipping; ?>">
            <div class="az-summary-row"><span>جمع محصولات</span><span><?php echo amuj_price($subtotal); ?></span></div>
            <div class="az-summary-row"><span>هزینه ارسال <small class="az-muted" data-az-ship-label>(پست پیشتاز)</small></span><span data-az-ship-cost><?php echo amuj_price($shipping); ?></span></div>
            <div class="az-summary-row az-co-minus" data-az-row-discount hidden><span>تخفیف</span><span></span></div>
            <div class="az-summary-row az-co-minus" data-az-row-credit hidden><span>کیف پول</span><span></span></div>
            <div class="az-summary-row is-total"><span>مبلغ قابل پرداخت</span><span data-az-pay><?php echo amuj_price($subtotal + $shipping); ?></span></div>
        </div>
        <a class="az-btn az-btn--block uk-margin-top" href="<?php echo amuj_url('thankyou'); ?>">پرداخت و ثبت سفارش</a>
        <p class="az-small az-muted uk-margin-small-top">با ثبت سفارش، <a class="az-link" href="#">قوانین و مقررات</a> را می‌پذیرید.</p>
    </aside>
</div>
