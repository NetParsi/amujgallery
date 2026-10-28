<?php
defined('_JEXEC') or die;

$products = amuj_products();
$id       = isset($_GET['id']) ? (int) $_GET['id'] : 1;
$id       = isset($products[$id]) ? $id : 1;
$p        = $products[$id];
$gallery  = [$p['img'], $p['img2'], ($p['img'] + 2), ($p['img'] + 5)];
?>
<div class="az-product">
    <aside class="az-product-desc">
        <h4>توضیحات</h4>
        <p><?php echo $p['name']; ?> با برش راحت و خطوط ساده. مناسب استفاده روزمره و قابل ست با بیشتر استایل‌ها. مدل قد ۱۷۵ سانتی‌متر است و سایز S پوشیده است.</p>

        <h4>جنس و مراقبت</h4>
        <ul>
            <li>۷۰٪ پشم، ۳۰٪ پلی‌استر</li>
            <li>شست‌وشوی خشک</li>
            <li>اتو با حرارت کم</li>
        </ul>

        <h4>کد محصول</h4>
        <p><?php echo amuj_fa('2406/' . str_pad($id, 3, '0', STR_PAD_LEFT) . '/800'); ?></p>
    </aside>

    <div class="az-product-gallery">
        <?php foreach ($gallery as $img) : ?>
            <img src="<?php echo amuj_img($img, 1200); ?>" alt="<?php echo $p['name']; ?>" loading="lazy">
        <?php endforeach; ?>
    </div>

    <aside class="az-product-buy">
        <h1><?php echo $p['name']; ?></h1>
        <div class="az-price"><?php echo amuj_price($p['price']); ?></div>

        <div class="az-small az-muted">رنگ: مشکی</div>
        <div class="az-colors" data-az-choice="single">
            <span class="az-swatch is-active" style="background:#000"></span>
            <span class="az-swatch" style="background:#c8b8a0"></span>
            <span class="az-swatch" style="background:#6b4f3a"></span>
        </div>

        <ul class="az-sizes" data-az-choice="single">
            <li><span>XS</span></li>
            <li><span>S</span></li>
            <li><span>M</span><span class="az-muted az-small">چند عدد باقی مانده</span></li>
            <li><span>L</span></li>
            <li class="is-out"><span>XL</span><span class="az-small">ناموجود</span></li>
        </ul>

        <button class="az-btn az-btn--block" type="button" data-az-add>افزودن به سبد خرید</button>

        <div class="az-buy-links">
            <a href="#">راهنمای سایز</a>
            <a href="#">موجودی در فروشگاه‌ها</a>
            <a href="<?php echo amuj_url('help'); ?>">ارسال، تعویض و مرجوعی</a>
        </div>
    </aside>
</div>

<section class="az-section">
    <div class="az-section-head"><h2>ست کنید با</h2></div>
    <div class="az-row-slider" uk-slider="finite: true">
        <ul class="uk-slider-items">
            <?php foreach ($products as $rid => $rp) : ?>
                <?php if ($rid !== $id) : ?><li><?php amuj_card($rid, $rp); ?></li><?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
