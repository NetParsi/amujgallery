<?php
defined('_JEXEC') or die;

$faq = [
    'سفارش من چند روزه به دستم می‌رسد؟'      => 'سفارش‌های تهران با پیک در همان روز و سایر شهرها با پست پیشتاز طی ۳ تا ۵ روز کاری تحویل داده می‌شوند.',
    'چطور سفارشم را پیگیری کنم؟'             => 'پس از ارسال، کد رهگیری پیامک می‌شود. همچنین از بخش «حساب کاربری > سفارش‌های من» وضعیت را ببینید.',
    'شرایط تعویض و مرجوعی چیست؟'             => 'تا ۱۴ روز پس از تحویل، در صورت سالم بودن محصول و داشتن برچسب، امکان تعویض یا مرجوعی وجود دارد.',
    'چطور سایز مناسب را انتخاب کنم؟'          => 'در صفحه هر محصول روی «راهنمای سایز» بزنید تا جدول اندازه‌ها را ببینید.',
    'روش‌های پرداخت کدام‌اند؟'                => 'پرداخت از طریق درگاه اینترنتی با کلیه کارت‌های عضو شتاب و کارت به کارت امکان‌پذیر است.',
];
?>
<div class="az-narrow">
    <h1 class="az-title">چطور می‌توانیم کمکتان کنیم؟</h1>
    <div class="az-help-grid">
        <a href="#"><strong>سفارش‌ها</strong><span class="az-muted">پیگیری، ویرایش و لغو سفارش</span></a>
        <a href="#"><strong>ارسال و تحویل</strong><span class="az-muted">روش‌ها، زمان و هزینه ارسال</span></a>
        <a href="#"><strong>تعویض و مرجوعی</strong><span class="az-muted">شرایط و مراحل بازگرداندن کالا</span></a>
        <a href="#"><strong>پرداخت</strong><span class="az-muted">روش‌های پرداخت و فاکتور</span></a>
    </div>

    <h2 class="az-title">سوالات متداول</h2>
    <ul class="az-faq" uk-accordion>
        <?php foreach ($faq as $q => $a) : ?>
            <li><a class="uk-accordion-title" href="#"><?php echo $q; ?></a><div class="uk-accordion-content"><p><?php echo $a; ?></p></div></li>
        <?php endforeach; ?>
    </ul>

    <div id="contact" class="uk-grid-large uk-child-width-1-2@m uk-margin-xlarge-top" uk-grid>
        <div>
            <h2 class="az-title">تماس با ما</h2>
            <p>تلفن پشتیبانی: <?php echo amuj_fa('021-00000000'); ?> (شنبه تا پنجشنبه، ۹ تا ۲۱)</p>
            <p>ایمیل: info@amujgallery.ir</p>
            <p>آدرس فروشگاه: تهران، ...</p>
        </div>
        <div>
            <h2 class="az-title">ارسال پیام</h2>
            <form onsubmit="return false">
                <div class="az-field"><input class="az-input" placeholder="نام و نام خانوادگی"></div>
                <div class="az-field"><input class="az-input" placeholder="ایمیل یا تلفن همراه"></div>
                <div class="az-field"><textarea class="az-textarea" placeholder="پیام شما"></textarea></div>
                <button class="az-btn" type="submit">ارسال</button>
            </form>
        </div>
    </div>
</div>
