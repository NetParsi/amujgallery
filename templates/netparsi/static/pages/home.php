<?php
defined('_JEXEC') or die;

// Dynamic home: the modules in position "home" (HikaShop category / product modules with the
// layouts in html/mod_hikacategories and html/mod_hikaproducts). The demo below shows while it is empty.
if (isset($this) && method_exists($this, 'countModules') && $this->countModules('home')) {
    echo '<jdoc:include type="modules" name="home" style="none" />';
    return;
}

// Portrait shop photos: three per slide side by side (one on mobile)
$slides = [
    ['imgs' => ['images/products/monogram/black.jpg', 'images/products/monogram/brown.jpg', 'images/products/suede/1.jpg'],
     'title' => 'کالکشن پاییز و زمستان', 'text' => 'کت‌های مونوگرام با چرم و جیر، برای روزهای سرد.', 'tab' => 'جدیدترین‌ها'],
    ['imgs' => ['images/products/cream/1.jpg', 'images/products/cream/2.jpg', 'images/products/cream/3.jpg'],
     'title' => 'کت‌های روشن بهاره', 'text' => 'رنگ‌های آرام و یراق طلایی.', 'tab' => 'کت'],
    ['imgs' => ['images/demo-product/harir-black.jpg', 'images/demo-product/harir-green.jpg', 'images/demo-product/harir-white.jpg'],
     'title' => 'پیراهن حریر', 'text' => 'سبک و روان، در سه رنگ.', 'tab' => 'پیراهن'],
    ['imgs' => ['images/products/bomber/1.jpg', 'images/products/bomber/2.jpg', 'images/products/bomber/3.jpg'],
     'title' => 'بامبر راه‌راه', 'text' => 'کلاسیک و روزمره.', 'tab' => 'حراج'],
];
$products = amuj_products();
?>
<?php
// Slide duration (ms). The progress line under the active tab fills over this time,
// then the next slide shows (see netparsi.js).
$slideDuration = 5000;
?>
<section class="az-hero" uk-slideshow="animation: fade; autoplay: false; finite: false" data-az-duration="<?php echo $slideDuration; ?>" style="--az-dur: <?php echo $slideDuration; ?>ms">
    <ul class="uk-slideshow-items">
        <?php foreach ($slides as $slide) : ?>
            <li class="az-hero-shade">
                <div class="az-hero-split">
                    <?php foreach ($slide['imgs'] as $img) : ?><img src="<?php echo amuj_img($img, 1200); ?>" alt=""><?php endforeach; ?>
                </div>
                <div class="az-hero-caption">
                    <h2><?php echo $slide['title']; ?></h2>
                    <p><?php echo $slide['text']; ?></p>
                    <a class="az-btn" href="<?php echo amuj_url('category'); ?>">مشاهده کالکشن</a>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    <button class="az-hero-nav az-hero-nav--prev" type="button" uk-slideshow-item="previous" aria-label="اسلاید قبلی">
        <svg viewBox="0 0 24 48" aria-hidden="true"><path d="M6 2l14 22L6 46"/></svg>
    </button>
    <button class="az-hero-nav az-hero-nav--next" type="button" uk-slideshow-item="next" aria-label="اسلاید بعدی">
        <svg viewBox="0 0 24 48" aria-hidden="true"><path d="M18 2L4 24l14 22"/></svg>
    </button>
    <ul class="az-hero-tabs">
        <?php foreach ($slides as $i => $slide) : ?>
            <li uk-slideshow-item="<?php echo $i; ?>">
                <a href="#"><?php echo $slide['tab']; ?></a>
                <span class="az-hero-progress" aria-hidden="true"><i></i></span>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<section class="az-section">
    <div class="az-section-head">
        <h2>جدیدترین‌ها</h2>
        <a href="<?php echo amuj_url('category'); ?>">مشاهده همه</a>
    </div>
    <div class="az-row-slider" uk-slider="finite: true">
        <ul class="uk-slider-items">
            <?php foreach ($products as $id => $p) : ?>
                <li><?php amuj_card($id, $p); ?></li>
            <?php endforeach; ?>
        </ul><?php amuj_slider_nav(); ?>
    </div>
</section>

<section class="az-section">
    <div class="az-editorial">
        <a href="<?php echo amuj_url('category'); ?>"><img src="<?php echo amuj_img('images/demo-product/harir-white.jpg', 1200); ?>" alt="" loading="lazy"><span>پیراهن و شومیز</span></a>
        <a href="<?php echo amuj_url('category'); ?>"><img src="<?php echo amuj_img('images/products/suede/1.jpg', 1200); ?>" alt="" loading="lazy"><span>کت و پالتو</span></a>
    </div>
</section>

<section class="az-section">
    <a class="az-wide-banner" href="<?php echo amuj_url('category'); ?>">
        <img src="<?php echo amuj_img('images/products/cream/2.jpg', 1920); ?>" alt="" loading="lazy">
        <div>
            <h3>AMUJ STUDIO</h3>
            <span class="az-link">کالکشن محدود را ببینید</span>
        </div>
    </a>
</section>

<section class="az-section">
    <div class="az-section-head">
        <h2>پرفروش‌های هفته</h2>
        <a href="<?php echo amuj_url('category'); ?>">مشاهده همه</a>
    </div>
    <div class="az-grid">
        <?php foreach (array_slice($products, 2, 4, true) as $id => $p) : ?>
            <?php amuj_card($id, $p); ?>
        <?php endforeach; ?>
    </div>
</section>
