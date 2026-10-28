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
        'سؤالات متداول'     => amuj_url('help'),
        'شرایط خرید و پرداخت' => amuj_url('policy', ['doc' => 'terms']),
        'ارسال و تحویل'     => amuj_url('policy', ['doc' => 'shipping']),
        'تعویض و مرجوعی'    => amuj_url('policy', ['doc' => 'returns']),
        'پیگیری سفارش'      => amuj_url('account'),
        'راهنمای سایز'      => amuj_url('help'),
    ]],
    'footer-2' => ['گالری آموژ', [
        'درباره ما'          => amuj_url('about'),
        'تماس با ما'         => amuj_url('contact'),
        'فروشگاه‌های حضوری' => amuj_url('about'),
        'فرصت‌های شغلی'     => amuj_url('about'),
        'قوانین و مقررات'   => amuj_url('policy', ['doc' => 'terms']),
        'حریم خصوصی'        => amuj_url('policy', ['doc' => 'privacy']),
    ]],
    'footer-3' => ['دسته‌بندی‌ها', array_combine(amuj_categories(), array_map(function ($c) {
        return amuj_url('category', ['cat' => $c]);
    }, amuj_categories()))],
];
$contact = amuj_contact(); // global variables, group «اطلاعات تماس»
$social = ['اینستاگرام' => $contact['instagram'], 'واتس‌اپ' => $contact['whatsapp']];
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
                                <li><span>تلفن و واتس‌اپ</span><a href="tel:<?php echo $contact['tel']; ?>" dir="ltr"><?php echo $contact['phone']; ?></a></li>
                                <li><span>اینستاگرام</span><a href="<?php echo $contact['instagram']; ?>" target="_blank" rel="noopener" dir="ltr"><?php echo $contact['instagram_handle']; ?></a></li>
                                <li><span>نشانی</span><?php echo $contact['address']; ?></li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="az-footer-mid">
            <ul class="az-social">
                <?php foreach ($social as $label => $url) : ?>
                    <li><a href="<?php echo $url; ?>" target="_blank" rel="noopener"><?php echo $label; ?></a></li>
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
