<?php
defined('_JEXEC') or die;

/*
 * After payment. ?status=failed shows the failed state (for demos).
 * Dynamic: HikaShop's checkout end / payment notification pages.
 */
$failed   = isset($_GET['status']) && $_GET['status'] === 'failed';
$products = amuj_products();
$items    = [13 => ['۳۸', 'مشکی'], 2 => ['۳۸', 'کرم']];
$subtotal = $products[13]['price'] + $products[2]['price'];
$shipping = 85000;
$paid     = $subtotal + $shipping;
$user     = amuj_current_user();
?>
<div class="az-thanks<?php echo $failed ? ' is-failed' : ''; ?>">
    <div class="az-status-icon" aria-hidden="true">
        <svg viewBox="0 0 96 96">
            <circle class="az-status-ring" cx="48" cy="48" r="44"/>
            <?php if ($failed) : ?>
                <path class="az-status-mark" d="M34 34l28 28M62 34L34 62"/>
            <?php else : ?>
                <path class="az-status-mark" d="M28 50l13 13 27-29"/>
            <?php endif; ?>
        </svg>
    </div>

    <?php if ($failed) : ?>
        <h1>پرداخت ناموفق بود</h1>
        <p class="az-muted">پرداخت سفارش شما تکمیل نشد و سفارش هنوز ثبت نشده است. کالاها تا ۳۰ دقیقه در سبد شما رزرو می‌مانند.</p>
        <div class="az-thanks-note">
            اگر مبلغی از حساب شما کسر شده، حداکثر تا ۷۲ ساعت آینده توسط بانک به حساب‌تان بازگردانده می‌شود.
        </div>
        <div class="az-thanks-actions">
            <a class="az-btn" href="<?php echo amuj_url('checkout'); ?>">تلاش مجدد پرداخت</a>
            <a class="az-btn az-btn--ghost" href="<?php echo amuj_url('cart'); ?>">بازگشت به سبد خرید</a>
        </div>
        <p class="az-small az-muted">نیاز به کمک دارید؟ با پشتیبانی تماس بگیرید: <a class="az-link" href="tel:<?php echo amuj_contact()['tel']; ?>" dir="ltr"><?php echo amuj_contact()['phone']; ?></a></p>
    <?php else : ?>
        <h1>سفارش شما با موفقیت ثبت شد</h1>
        <p class="az-muted">جزئیات سفارش و کد رهگیری مرسوله به تلفن همراه شما پیامک می‌شود.</p>

        <div class="az-thanks-card">
            <div class="az-thanks-meta">
                <div><span>شماره سفارش</span><strong dir="ltr"><?php echo amuj_fa('AM-140508-2731'); ?></strong></div>
                <div><span>کد پیگیری بانکی</span><strong dir="ltr"><?php echo amuj_fa('618273940512'); ?></strong></div>
                <div><span>مبلغ پرداخت‌شده</span><strong><?php echo amuj_price($paid); ?></strong></div>
            </div>

            <div class="az-thanks-items">
                <?php foreach ($items as $id => [$size, $color]) : $p = $products[$id]; ?>
                    <div class="az-summary-item">
                        <img src="<?php echo amuj_img($p['img'], 200); ?>" alt="">
                        <div>
                            <div><?php echo $p['name']; ?></div>
                            <div class="az-muted"><?php echo $color; ?> | سایز <?php echo $size; ?> | تعداد ۱</div>
                            <div><?php echo amuj_price($p['price']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="az-thanks-ship">
                <div><span>ارسال به</span>سارا محمدی — تهران، ولنجک، خیابان چهاردهم</div>
                <div><span>روش ارسال</span>پست پیشتاز — ۳ تا ۵ روز کاری</div>
            </div>
        </div>

        <?php if ($user) : ?>
            <div class="az-thanks-club">
                <span>این خرید در باشگاه مشتریان ثبت شد و به سطح <strong><?php echo $user['next_level']; ?></strong> نزدیک‌تر شدید.</span>
                <div class="az-club-bar"><span style="--p: <?php echo $user['progress']; ?>%"></span></div>
            </div>
        <?php endif; ?>

        <div class="az-thanks-actions">
            <a class="az-btn" href="<?php echo amuj_url('order'); ?>">پیگیری سفارش</a>
            <a class="az-btn az-btn--ghost" href="<?php echo amuj_url('home'); ?>">ادامه خرید</a>
        </div>
    <?php endif; ?>
</div>
