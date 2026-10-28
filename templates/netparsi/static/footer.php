<?php
defined('_JEXEC') or die;

/*
 * Footer slots are Joomla module positions (templateDetails.xml). Empty position = static demo:
 * footer-1..3 = Menu modules, footer-contact / footer-trust / newsletter = Custom modules.
 */
$hasModules = function ($position) {
    return isset($this) && method_exists($this, 'countModules') && $this->countModules($position);
};

$columns = [
    'footer-1' => ['خرید آنلاین', [
        'راهنمای خرید'      => amuj_url('help'),
        'شیوه‌های پرداخت'   => amuj_url('help'),
        'ارسال و تحویل'     => amuj_url('help'),
        'تعویض و مرجوعی'    => amuj_url('help'),
        'پیگیری سفارش'      => amuj_url('account'),
        'راهنمای سایز'      => amuj_url('help'),
    ]],
    'footer-2' => ['گالری آموژ', [
        'درباره ما'          => amuj_url('about'),
        'تماس با ما'         => amuj_url('help') . '#contact',
        'فروشگاه‌های حضوری' => amuj_url('about'),
        'فرصت‌های شغلی'     => amuj_url('about'),
        'قوانین و مقررات'   => amuj_url('help'),
        'حریم خصوصی'        => amuj_url('help'),
    ]],
    'footer-3' => ['حساب من', [
        'ورود / ثبت‌نام'     => amuj_url('login'),
        'سفارش‌های من'       => amuj_url('account'),
        'علاقه‌مندی‌ها'      => amuj_url('account'),
        'آدرس‌ها'            => amuj_url('account'),
        'سبد خرید'           => amuj_url('cart'),
    ]],
];
$social = ['اینستاگرام' => '#', 'تلگرام' => '#', 'واتس‌اپ' => '#', 'پینترست' => '#', 'آپارات' => '#'];
?>
<footer class="az-footer">
    <div class="az-footer-inner">
        <div class="az-footer-top">
            <div class="az-footer-brand">
                <a class="az-logo" href="<?php echo amuj_url('home'); ?>" aria-label="گالری آموژ"><?php echo amuj_logo(); ?></a>
                <?php if ($hasModules('newsletter')) : ?>
                    <jdoc:include type="modules" name="newsletter" style="none" />
                <?php else : ?>
                    <div class="az-newsletter">
                        <h3>عضویت در خبرنامه</h3>
                        <p>از کالکشن‌های جدید، تخفیف‌ها و رویدادهای آموژ زودتر از همه باخبر شوید.</p>
                        <form class="az-newsletter-form" onsubmit="return false">
                            <input class="az-input" type="email" placeholder="ایمیل یا شماره موبایل" aria-label="ایمیل یا شماره موبایل">
                            <button class="az-btn" type="submit">عضویت</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <div class="az-footer-cols">
                <?php foreach ($columns as $position => [$title, $links]) : ?>
                    <div class="az-footer-col">
                        <button class="az-footer-head" type="button"><?php echo $title; ?><span class="az-footer-plus" aria-hidden="true"></span></button>
                        <div class="az-footer-body">
                            <?php if ($hasModules($position)) : ?>
                                <jdoc:include type="modules" name="<?php echo $position; ?>" style="none" />
                            <?php else : ?>
                                <ul>
                                    <?php foreach ($links as $label => $url) : ?>
                                        <li><a href="<?php echo $url; ?>"><?php echo $label; ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="az-footer-col">
                    <button class="az-footer-head" type="button">ارتباط با ما<span class="az-footer-plus" aria-hidden="true"></span></button>
                    <div class="az-footer-body">
                        <?php if ($hasModules('footer-contact')) : ?>
                            <jdoc:include type="modules" name="footer-contact" style="none" />
                        <?php else : ?>
                            <ul class="az-footer-contact">
                                <li><span>پشتیبانی</span><a href="tel:+982100000000" dir="ltr"><?php echo amuj_fa('021 - 0000 0000'); ?></a></li>
                                <li><span>ساعت پاسخگویی</span>شنبه تا پنجشنبه، ۹ تا ۲۱</li>
                                <li><span>ایمیل</span><a href="mailto:info@amujgallery.ir">info@amujgallery.ir</a></li>
                                <li><span>فروشگاه</span>تهران، ...</li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="az-footer-mid">
            <ul class="az-social">
                <?php foreach ($social as $label => $url) : ?>
                    <li><a href="<?php echo $url; ?>"><?php echo $label; ?></a></li>
                <?php endforeach; ?>
            </ul>
            <div class="az-trust">
                <?php if ($hasModules('footer-trust')) : ?>
                    <jdoc:include type="modules" name="footer-trust" style="none" />
                <?php else : ?>
                    <span class="az-trust-badge">نماد اعتماد<br>الکترونیکی</span>
                    <span class="az-trust-badge">ساماندهی</span>
                    <span class="az-trust-badge">درگاه امن<br>شاپرک</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="az-footer-bottom">
            <p class="az-copy">© <?php echo amuj_fa(1405); ?> گالری آموژ — تمامی حقوق محفوظ است.</p>
            <p class="az-copy">ارسال به سراسر ایران · ضمانت اصالت کالا · ۱۴ روز مهلت مرجوعی</p>
        </div>
    </div>
</footer>
