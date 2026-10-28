<?php
defined('_JEXEC') or die;

/* Contact us. Dynamic: Joomla contact (com_contact) or a form extension; details below are the real shop details. */
$address = 'تهران، ولنجک، خیابان چهاردهم، پلاک ۱۹، برج هوما، طبقه ۹، واحد ۹۰۴';
$query   = rawurlencode('برج هوما ولنجک تهران');
?>
<div class="az-contact">
    <header class="az-page-head">
        <span class="az-num">تماس با ما</span>
        <h1>خوشحال می‌شویم از شما بشنویم</h1>
        <p class="az-muted">برای سؤال درباره سفارش، مشاوره سایز یا هماهنگی بازدید از فروشگاه، از هر راهی که راحت‌ترید با ما در ارتباط باشید.</p>
    </header>

    <div class="az-contact-grid">
        <div class="az-contact-cards">
            <a class="az-contact-card" href="tel:+989354466989">
                <span>تلفن تماس</span>
                <strong dir="ltr">۰۹۳۵۴۴۶۶۹۸۹</strong>
            </a>
            <a class="az-contact-card" href="https://wa.me/989354466989" target="_blank" rel="noopener">
                <span>واتس‌اپ</span>
                <strong>گفت‌وگو در واتس‌اپ</strong>
            </a>
            <a class="az-contact-card" href="https://www.instagram.com/amuj.gallery" target="_blank" rel="noopener">
                <span>اینستاگرام</span>
                <strong dir="ltr">@amuj.gallery</strong>
            </a>
            <div class="az-contact-card az-contact-card--wide">
                <span>نشانی فروشگاه</span>
                <strong><?php echo $address; ?></strong>
                <div class="az-contact-maps">
                    <a href="https://neshan.org/maps/search/<?php echo $query; ?>" target="_blank" rel="noopener">مسیریابی با نشان</a>
                    <a href="https://balad.ir/search?query=<?php echo $query; ?>" target="_blank" rel="noopener">مسیریابی با بلد</a>
                    <a href="https://www.google.com/maps/search/<?php echo $query; ?>" target="_blank" rel="noopener">Google Maps</a>
                </div>
            </div>
        </div>

        <form class="az-contact-form" data-az-contact novalidate>
            <h2 class="az-title">ارسال پیام</h2>
            <div class="az-field"><label for="c-name">نام و نام خانوادگی</label><input class="az-input" id="c-name" name="name" required></div>
            <div class="az-field"><label for="c-phone">شماره موبایل</label><input class="az-input" id="c-phone" name="phone" inputmode="numeric" dir="ltr" required></div>
            <div class="az-field"><label for="c-topic">موضوع</label>
                <select class="az-select" id="c-topic" name="topic">
                    <option>پیگیری سفارش</option>
                    <option>مشاوره سایز</option>
                    <option>تعویض و مرجوعی</option>
                    <option>هماهنگی بازدید از فروشگاه</option>
                    <option>سایر موارد</option>
                </select>
            </div>
            <div class="az-field"><label for="c-msg">پیام شما</label><textarea class="az-textarea" id="c-msg" name="message" required></textarea></div>
            <p class="az-auth-error" data-error></p>
            <button class="az-btn" type="submit">ارسال پیام</button>
            <div class="az-contact-done" hidden>
                <strong>پیام شما ارسال شد</strong>
                <span class="az-muted">همکاران ما به‌زودی با شما تماس می‌گیرند.</span>
            </div>
        </form>
    </div>
</div>
