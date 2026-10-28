<?php
defined('_JEXEC') or die;
?>
<footer class="az-footer">
    <div class="az-newsletter">
        <h3>عضویت در خبرنامه</h3>
        <p>از کالکشن‌های جدید، تخفیف‌ها و رویدادهای آموژ زودتر از همه باخبر شوید.</p>
        <form class="az-newsletter-form" onsubmit="return false">
            <input class="az-input" type="email" placeholder="ایمیل">
            <button class="az-btn" type="submit">عضویت</button>
        </form>
    </div>
    <ul class="az-social">
        <li><a href="#">اینستاگرام</a></li>
        <li><a href="#">تلگرام</a></li>
        <li><a href="#">آپارات</a></li>
        <li><a href="#">پینترست</a></li>
        <li><a href="#">لینکدین</a></li>
    </ul>
    <ul class="az-footer-links">
        <li><a href="<?php echo amuj_url('help'); ?>">راهنمای خرید</a></li>
        <li><a href="<?php echo amuj_url('help'); ?>">ارسال و تحویل</a></li>
        <li><a href="<?php echo amuj_url('help'); ?>">مرجوعی و تعویض</a></li>
        <li><a href="<?php echo amuj_url('about'); ?>">درباره ما</a></li>
        <li><a href="<?php echo amuj_url('help'); ?>#contact">تماس با ما</a></li>
        <li><a href="#">قوانین و حریم خصوصی</a></li>
    </ul>
    <p class="az-copy">© <?php echo amuj_fa(1405); ?> گالری آموژ — تمامی حقوق محفوظ است.</p>
</footer>
