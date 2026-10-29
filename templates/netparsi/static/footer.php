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
        'پیگیری سفارش'      => amuj_url('orders'),
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
                        <!-- Saved by the "فرم تماس آموژ" plugin (ajax/amujcontact) in the table amuj_newsletter -->
                        <form class="az-newsletter-form" data-az-newsletter method="post" novalidate
                              action="<?php echo AMUJ_BASE; ?>index.php?option=com_ajax&amp;group=ajax&amp;plugin=amujcontact&amp;format=raw">
                            <input class="az-input" type="text" name="contact" inputmode="email" dir="ltr" maxlength="191" placeholder="ایمیل یا شماره موبایل" aria-label="ایمیل یا شماره موبایل">
                            <button class="az-btn" type="submit">عضویت</button>
                            <input type="hidden" name="form" value="newsletter">
                            <input type="hidden" name="<?php echo class_exists('\Joomla\CMS\Session\Session') ? \Joomla\CMS\Session\Session::getFormToken() : 'token'; ?>" value="1">
                            <span hidden aria-hidden="true"><input name="website" tabindex="-1" autocomplete="off"></span>
                        </form>
                        <p class="az-newsletter-msg" data-az-newsletter-msg role="status"></p>
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
                    <?php
                    // Badge images in images/trust/ (enamad.png / shaparak.png, or .svg / .webp); without an image the text placeholder stays.
                    // Enamad's own link (with its id and code) comes from the global variable "enamad_link".
                    $trust = [
                        'enamad'   => ['نماد اعتماد الکترونیکی', trim((string) amuj_var('enamad_link', '')) ?: 'https://enamad.ir', 'نماد اعتماد<br>الکترونیکی'],
                        'shaparak' => ['درگاه امن شاپرک', 'https://www.shaparak.ir', 'درگاه امن<br>شاپرک'],
                    ];
                    foreach ($trust as $key => [$alt, $href, $text]) :
                        $file = '';
                        foreach (['png', 'svg', 'webp', 'jpg'] as $ext) {
                            if (is_file(AMUJ_ROOT . '/images/trust/' . $key . '.' . $ext)) {
                                $file = 'images/trust/' . $key . '.' . $ext;
                                break;
                            }
                        }
                        ?>
                        <a class="az-trust-badge<?php echo $file ? ' has-img' : ''; ?>" href="<?php echo htmlspecialchars($href); ?>" target="_blank" rel="noopener" referrerpolicy="origin" title="<?php echo $alt; ?>">
                            <?php if ($file) : ?><img src="<?php echo AMUJ_BASE . $file; ?>" alt="<?php echo $alt; ?>" loading="lazy"><?php else : echo $text; endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="az-footer-bottom">
            <p class="az-copy">© <?php echo amuj_fa(1405); ?> گالری آموژ — تمامی حقوق محفوظ است.</p>
            <p class="az-copy az-credit-line">طراحی و توسعه توسط
                <a class="az-credit" href="https://netparsi.com" target="_blank" rel="noopener" aria-label="نت پارسی"><?php
                    $npLogo = AMUJ_ROOT . '/images/netparsi-logo.svg';
                    echo is_file($npLogo) ? file_get_contents($npLogo) : 'نت پارسی'; ?></a></p>
        </div>
    </div>
</footer>

<?php
// Fixed call-to-action buttons: WhatsApp chat and the shop's address (routing with Neshan / Google Maps).
// Not on the checkout and payment pages, where the customer should stay in the purchase.
$ctaHide = \Joomla\CMS\Factory::getApplication()->getInput()->getCmd('option') === 'com_hikashop'
    && \Joomla\CMS\Factory::getApplication()->getInput()->getCmd('ctrl', \Joomla\CMS\Factory::getApplication()->getInput()->getCmd('view')) === 'checkout';
if (!$ctaHide) :
    $mapQuery = rawurlencode($contact['map']);
    ?>
    <div class="az-cta" aria-label="ارتباط سریع">
        <a class="az-cta-btn az-cta-whatsapp" href="<?php echo htmlspecialchars($contact['whatsapp']); ?>" target="_blank" rel="noopener" aria-label="گفتگو در واتساپ">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.6a9.4 9.4 0 0 0-8.1 14.1L2.6 21.4l4.8-1.3A9.4 9.4 0 1 0 12 2.6z"/><path d="M8.6 7.9c.2-.4.5-.4.8-.4h.6c.2 0 .4.1.5.4l.8 1.9c.1.2 0 .5-.1.6l-.6.7c-.1.2-.1.4 0 .6.6 1.1 1.5 2 2.6 2.6.2.1.4.1.6 0l.7-.6c.2-.2.4-.2.6-.1l1.9.8c.3.1.4.3.4.5v.6c0 .3 0 .6-.4.8-.6.4-1.4.6-2.1.4-2.8-.8-5-3-5.8-5.8-.2-.7 0-1.5.5-2z" fill="currentColor" stroke="none"/></svg>
            <span>واتساپ</span>
        </a>
        <button class="az-cta-btn az-cta-address" type="button" aria-label="آدرس فروشگاه" aria-expanded="false" aria-controls="az-cta-drop" data-az-cta-toggle>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21.5s-7-6.6-7-12a7 7 0 0 1 14 0c0 5.4-7 12-7 12z"/><circle cx="12" cy="9.5" r="2.6"/></svg>
            <span>آدرس</span>
        </button>
        <div class="az-cta-drop" id="az-cta-drop" hidden>
            <strong>فروشگاه حضوری گالری آموژ</strong>
            <p><?php echo htmlspecialchars($contact['address']); ?></p>
            <div class="az-cta-maps">
                <a href="https://neshan.org/maps/search/<?php echo $mapQuery; ?>" target="_blank" rel="noopener">مسیریابی با نشان</a>
                <a href="https://www.google.com/maps/search/?api=1&amp;query=<?php echo $mapQuery; ?>" target="_blank" rel="noopener">Google Maps</a>
            </div>
            <a class="az-cta-call" href="tel:<?php echo $contact['tel']; ?>" dir="ltr"><?php echo $contact['phone']; ?></a>
        </div>
    </div>
<?php endif; ?>
