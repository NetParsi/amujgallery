<?php
defined('_JEXEC') or die;

/* About us. Dynamic: a Joomla article (text) + its intro/full images. Copy below is editable sample text. */
$values = [
    ['کیفیت در جزئیات', 'پارچه‌ها را با دقت انتخاب می‌کنیم و هر قطعه پیش از ارسال یک بار دیگر کنترل می‌شود.'],
    ['طراحی ماندگار', 'به‌جای دنبال کردن موج‌های زودگذر، لباس‌هایی می‌سازیم که سال‌ها در کمد شما بمانند.'],
    ['تجربه‌ای آسان', 'از انتخاب سایز تا تحویل و تعویض، همه‌چیز را ساده و شفاف نگه می‌داریم.'],
];
?>
<section class="az-about-hero">
    <img src="<?php echo amuj_img('images/demo-product/harir-green.jpg', 1920); ?>" alt="">
    <div class="az-about-hero-text">
        <span>درباره ما</span>
        <h1>گالری آموژ</h1>
    </div>
</section>

<section class="az-about-intro">
    <p class="az-about-lead">آموژ از یک ایده ساده شروع شد: پوشاک زنانه‌ای که هم امروزی باشد و هم با گذر فصل‌ها کهنه نشود.</p>
    <div class="az-about-cols">
        <p>ما مجموعه‌ای از مانتو، کت، پالتو، پیراهن و ست‌های روزمره و رسمی را طراحی و انتخاب می‌کنیم که با خطوط ساده و رنگ‌های آرام، به‌راحتی با هم ترکیب شوند. هر کالکشن با تمرکز بر برش درست، پارچه باکیفیت و راحتی در استفاده روزمره آماده می‌شود.</p>
        <p>باور داریم خرید لباس باید تجربه‌ای لذت‌بخش باشد؛ برای همین فروشگاه آنلاین آموژ را طوری ساخته‌ایم که بتوانید با آرامش ببینید، مقایسه کنید و انتخاب کنید. اعضای باشگاه مشتریان آموژ هم با هر خرید به سطح‌های بالاتر و مزایای بیشتر نزدیک می‌شوند.</p>
    </div>
</section>

<section class="az-about-values">
    <?php foreach ($values as $i => [$title, $text]) : ?>
        <div class="az-about-value">
            <span class="az-num">|<?php echo amuj_fa(str_pad($i + 1, 2, '0', STR_PAD_LEFT)); ?>|</span>
            <h3><?php echo $title; ?></h3>
            <p><?php echo $text; ?></p>
        </div>
    <?php endforeach; ?>
</section>

<section class="az-about-split">
    <div class="az-about-split-img"><img src="<?php echo amuj_img('images/demo-product/harir-white.jpg', 1200); ?>" alt="" loading="lazy"></div>
    <div class="az-about-split-text">
        <span class="az-num">فروشگاه حضوری</span>
        <h2>از نزدیک ببینید و امتحان کنید</h2>
        <p><?php echo amuj_contact()['address']; ?></p>
        <p class="az-muted">برای هماهنگی بازدید یا مشاوره سایز، از طریق تلفن یا واتس‌اپ با ما در تماس باشید.</p>
        <div class="az-about-actions">
            <a class="az-btn" href="<?php echo amuj_url('contact'); ?>">تماس و مسیریابی</a>
            <a class="az-btn az-btn--ghost" href="<?php echo amuj_url('category'); ?>">مشاهده کالکشن</a>
        </div>
    </div>
</section>
