<?php
defined('_JEXEC') or die;

$products = amuj_products();
$id       = isset($_GET['id']) ? (int) $_GET['id'] : 13;
$id       = isset($products[$id]) ? $id : 13;
$p        = $products[$id];

// Product data with fallbacks for the older demo items
$colorNames  = $p['color_names'] ?? ['مشکی', 'بژ', 'قهوه‌ای'];
$colorHexes  = isset($p['color_names']) ? $p['colors'] : ['#000', '#c8b8a0', '#6b4f3a'];
$activeColor = $p['active_color'] ?? 0;
$sizes       = $p['sizes'] ?? ['XS', 'S', 'M', 'L', 'XL'];
$desc        = $p['desc'] ?? $p['name'] . ' با برش راحت و خطوط ساده. مناسب استفاده روزمره و قابل ست با بیشتر استایل‌ها.';
$details     = $p['details'] ?? ['۷۰٪ پشم، ۳۰٪ پلی‌استر', 'شست‌وشوی خشک', 'اتو با حرارت کم'];
$code        = $p['code'] ?? '2406/' . str_pad($id, 3, '0', STR_PAD_LEFT) . '/800';

// Gallery: one photo per color (selected color first) or the generic demo set
if (!empty($p['color_images'])) {
    $gallery = [];
    foreach ($p['color_images'] as $i => $img) {
        $gallery[$i] = $img;
    }
    $gallery = [$activeColor => $gallery[$activeColor]] + $gallery;
} elseif (!empty($p['gallery'])) {
    $gallery = $p['gallery'];
} else {
    $gallery = [$p['img'], $p['img2']];
}
$singleSize = count($sizes) === 1;
?>
<div class="az-product">
    <aside class="az-product-desc">
        <h4>توضیحات</h4>
        <p><?php echo $desc; ?></p>

        <h4>مشخصات</h4>
        <ul>
            <?php foreach ($details as $line) : ?>
                <li><?php echo $line; ?></li>
            <?php endforeach; ?>
        </ul>

        <h4>کد محصول</h4>
        <p dir="ltr" class="uk-text-right"><?php echo amuj_fa($code); ?></p>
    </aside>

    <div class="az-product-gallery" data-az-gallery uk-lightbox="animation: fade; nav: thumbnav">
        <?php foreach ($gallery as $colorIndex => $img) : ?>
            <a class="az-zoom" href="<?php echo amuj_img($img, 2000); ?>" data-color="<?php echo $colorIndex; ?>" data-caption="<?php echo $p['name']; ?>" aria-label="بزرگ‌نمایی عکس">
                <img src="<?php echo amuj_img($img, 1200); ?>" alt="<?php echo $p['name'] . (isset($p['color_images']) ? ' — ' . $colorNames[$colorIndex] : ''); ?>"<?php echo (!empty($p['color_images']) || $colorIndex === array_key_first($gallery)) ? '' : ' loading="lazy"'; ?>>
            </a>
        <?php endforeach; ?>
    </div>

    <aside class="az-product-buy" data-az-product data-name="<?php echo $p['name']; ?>" data-price="<?php echo amuj_price($p['price']); ?>" data-img="<?php echo amuj_img($p['img'], 300); ?>">
        <h1><?php echo $p['name']; ?></h1>
        <div class="az-price"><?php echo amuj_price($p['price']); ?></div>

        <div class="az-small az-muted">رنگ: <span data-az-color-label><?php echo $colorNames[$activeColor]; ?></span></div>
        <div class="az-colors" data-az-choice="single">
            <?php foreach ($colorNames as $i => $name) : ?>
                <button type="button" class="az-swatch<?php echo $i === $activeColor ? ' is-active' : ''; ?>" style="background:<?php echo $colorHexes[$i]; ?>"
                        data-az-color="<?php echo $i; ?>" data-name="<?php echo $name; ?>" aria-label="<?php echo $name; ?>"></button>
            <?php endforeach; ?>
        </div>

        <ul class="az-sizes" data-az-choice="single" data-az-sizes>
            <?php foreach ($sizes as $i => $size) :
                $out  = !$singleSize && $i === 4;
                $last = !$singleSize && $i === 2; ?>
                <li class="<?php echo trim(($singleSize ? 'is-active ' : '') . ($out ? 'is-out' : '')); ?>">
                    <span><?php echo $size; ?></span>
                    <?php if ($out) : ?><span class="az-small">ناموجود</span><?php elseif ($last) : ?><span class="az-muted az-small">چند عدد باقی مانده</span><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <p class="az-size-error" data-az-size-error hidden>لطفاً سایز را انتخاب کنید.</p>
        <button class="az-btn az-btn--block" type="button" data-az-add>افزودن به سبد خرید</button>

        <div class="az-buy-links">
            <a href="#az-size-guide" uk-toggle="target: #az-size-guide">راهنمای سایز</a>
            <a href="<?php echo amuj_url('policy', ['doc' => 'returns']); ?>">ارسال، تعویض و مرجوعی</a>
        </div>
    </aside>
</div>

<?php
// Size guide (cm). Dynamic: a Joomla article/module shared by all products.
$guide = [
    ['۳۶', '۸۴', '۶۶', '۹۲', '۵۹'],
    ['۳۸', '۸۸', '۷۰', '۹۶', '۶۰'],
    ['۴۰', '۹۲', '۷۴', '۱۰۰', '۶۱'],
    ['۴۲', '۹۶', '۷۸', '۱۰۴', '۶۲'],
];
?>
<div id="az-size-guide" uk-offcanvas="overlay: true; flip: true">
    <div class="uk-offcanvas-bar az-user-panel az-size-panel">
        <div class="az-user-top">
            <div class="az-user-hello">راهنمای سایز</div>
            <button class="az-user-close uk-offcanvas-close" type="button" aria-label="بستن"><svg viewBox="0 0 38 38" aria-hidden="true"><path d="M2 2l34 34M36 2L2 36"/></svg></button>
        </div>
        <p class="az-muted az-small">اندازه‌ها به سانتی‌متر و مربوط به بدن است. اگر بین دو سایز هستید، سایز بزرگ‌تر را انتخاب کنید.</p>
        <table class="az-size-table">
            <thead><tr><th>سایز</th><th>دور سینه</th><th>دور کمر</th><th>دور باسن</th><th>قد آستین</th></tr></thead>
            <tbody>
                <?php foreach ($guide as $row) : ?>
                    <tr><?php foreach ($row as $c => $cell) : ?><<?php echo $c ? 'td' : 'th'; ?>><?php echo $cell; ?></<?php echo $c ? 'td' : 'th'; ?>><?php endforeach; ?></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h4 class="az-size-how">چطور اندازه بگیریم؟</h4>
        <ul class="az-size-tips">
            <li><strong>دور سینه:</strong> متر را از برجسته‌ترین قسمت سینه، موازی با زمین دور بدن بچرخانید.</li>
            <li><strong>دور کمر:</strong> باریک‌ترین قسمت کمر، کمی بالاتر از ناف.</li>
            <li><strong>دور باسن:</strong> برجسته‌ترین قسمت باسن، با پاهای جفت.</li>
        </ul>
        <div class="az-policy-help">برای مشاوره سایز در واتس‌اپ پیام دهید: <a class="az-link" href="https://wa.me/989354466989" target="_blank" rel="noopener" dir="ltr">۰۹۳۵۴۴۶۶۹۸۹</a></div>
    </div>
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
