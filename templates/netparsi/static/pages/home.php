<?php
defined('_JEXEC') or die;

$slides = [
    ['img' => 1,  'title' => 'کالکشن پاییز ۱۴۰۵',  'text' => 'خطوط ساده، پارچه‌های گرم و رنگ‌های خنثی.', 'tab' => 'جدیدترین‌ها'],
    ['img' => 4,  'title' => 'مانتو و بارانی',      'text' => 'برش‌های بلند و مینیمال برای هر روز.',      'tab' => 'مانتو'],
    ['img' => 8,  'title' => 'کت و پالتو',          'text' => 'پارچه‌های پشمی و دوخت دقیق برای فصل سرما.', 'tab' => 'پالتو'],
    ['img' => 5,  'title' => 'حراج فصل',           'text' => 'تا ۵۰٪ تخفیف روی منتخب محصولات.',        'tab' => 'حراج'],
];
$products = amuj_products();
?>
<section class="az-hero" uk-slideshow="animation: fade; autoplay: true; autoplay-interval: 6000; pause-on-hover: false">
    <ul class="uk-slideshow-items">
        <?php foreach ($slides as $slide) : ?>
            <li class="az-hero-shade">
                <img src="<?php echo amuj_img($slide['img'], 1920); ?>" alt="" uk-cover>
                <div class="az-hero-caption">
                    <h2><?php echo $slide['title']; ?></h2>
                    <p><?php echo $slide['text']; ?></p>
                    <a class="az-btn" href="<?php echo amuj_url('category'); ?>">مشاهده کالکشن</a>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    <ul class="az-hero-tabs">
        <?php foreach ($slides as $i => $slide) : ?>
            <li uk-slideshow-item="<?php echo $i; ?>"><a href="#"><?php echo $slide['tab']; ?></a></li>
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
        </ul>
    </div>
</section>

<section class="az-section">
    <div class="az-editorial">
        <a href="<?php echo amuj_url('category'); ?>"><img src="<?php echo amuj_img(5, 1200); ?>" alt="" loading="lazy"><span>پیراهن و شومیز</span></a>
        <a href="<?php echo amuj_url('category'); ?>"><img src="<?php echo amuj_img(8, 1200); ?>" alt="" loading="lazy"><span>کت و پالتو</span></a>
    </div>
</section>

<section class="az-section">
    <a class="az-wide-banner" href="<?php echo amuj_url('category'); ?>">
        <img src="<?php echo amuj_img(20, 1920); ?>" alt="" loading="lazy">
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
        <?php foreach (array_slice($products, 4, 4, true) as $id => $p) : ?>
            <?php amuj_card($id, $p); ?>
        <?php endforeach; ?>
    </div>
</section>
