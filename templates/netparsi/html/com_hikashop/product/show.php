<?php
/**
 * HikaShop product page in the Amuj design: description | gallery | buy box.
 * Colour + size pick the HikaShop variant; "افزودن به سبد خرید" posts it to HikaShop's cart (AJAX).
 * Popups / AJAX calls keep HikaShop's own layout.
 */
defined('_JEXEC') or die;

$input = \Joomla\CMS\Factory::getApplication()->getInput();
if (empty($this->element->product_id) || in_array($input->getCmd('tmpl'), ['component', 'raw', 'ajax'], true)) {
    require JPATH_ROOT . '/components/com_hikashop/views/product/tmpl/show.php';
    return;
}

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

$mainId = (int) ($this->element->product_parent_id ?: $this->element->product_id);
$all    = amuj_hika_products([$mainId]);
if (!$all) {
    require JPATH_ROOT . '/components/com_hikashop/views/product/tmpl/show.php';
    return;
}
$p = $all[$mainId];

$colorNames = $p['color_names'];
$colorIds   = $p['color_ids'];
$hasSizes   = !empty($p['size_ids']);
$singleSize = count($p['sizes']) === 1;

// Gallery: one photo per colour when the colours have their own photos, else the product photos
if (count($p['color_images']) > 1) {
    $gallery = [];
    foreach ($colorIds as $i => $cid) {
        if (isset($p['color_images'][$cid])) {
            $gallery[$i] = $p['color_images'][$cid];
        }
    }
} else {
    $gallery = $p['images'];
}

// Variants for the buy box: "colourId|sizeId" => [variant id, available]
$variantJson = htmlspecialchars(json_encode($p['variants']), ENT_QUOTES);
$cartUrl     = Uri::root() . 'index.php?option=com_hikashop&ctrl=product&task=updatecart&tmpl=raw';

// "Pair it with": other products of the same categories
$db = Factory::getDbo();
$related = $db->setQuery('SELECT DISTINCT pc2.product_id FROM ' . hikashop_table('product_category') . ' pc'
    . ' JOIN ' . hikashop_table('product_category') . ' pc2 ON pc2.category_id = pc.category_id AND pc2.product_id != pc.product_id'
    . ' JOIN ' . hikashop_table('product') . ' p ON p.product_id = pc2.product_id AND p.product_published = 1'
    . ' WHERE pc.product_id = ' . $mainId . ' LIMIT 12')->loadColumn();
if (count($related) < 4) {
    $related = array_merge($related, $db->setQuery('SELECT product_id FROM ' . hikashop_table('product')
        . ' WHERE product_type = "main" AND product_published = 1 AND product_id != ' . $mainId . ' ORDER BY product_created DESC LIMIT 8')->loadColumn());
}
$related = amuj_hika_products(array_slice(array_unique($related), 0, 8));
?>
<div class="az-product">
    <aside class="az-product-desc">
        <?php if (trim(strip_tags($p['desc']))) : ?>
            <h4>توضیحات</h4>
            <div class="az-product-text"><?php echo \Joomla\CMS\HTML\HTMLHelper::_('content.prepare', $p['desc']); ?></div>
        <?php endif; ?>

        <?php if ($colorNames || $hasSizes) : ?>
            <h4>مشخصات</h4>
            <ul>
                <?php if ($colorNames) : ?><li>رنگ‌بندی: <?php echo implode('، ', $colorNames); ?></li><?php endif; ?>
                <?php if ($hasSizes) : ?><li>سایزها: <?php echo implode('، ', $p['sizes']); ?></li><?php endif; ?>
            </ul>
        <?php endif; ?>

        <h4>کد محصول</h4>
        <p dir="ltr" class="uk-text-right"><?php echo amuj_fa($p['code']); ?></p>
    </aside>

    <div class="az-product-gallery" data-az-gallery uk-lightbox="animation: fade; nav: thumbnav">
        <?php $first = true; foreach ($gallery as $colorIndex => $img) : ?>
            <a class="az-zoom" href="<?php echo amuj_img($img); ?>" data-color="<?php echo $colorIndex; ?>" data-caption="<?php echo $this->escape($p['name']); ?>" aria-label="بزرگ‌نمایی عکس">
                <img src="<?php echo amuj_img($img); ?>" alt="<?php echo $this->escape($p['name'] . (count($p['color_images']) > 1 ? ' — ' . $colorNames[$colorIndex] : '')); ?>"<?php echo $first || count($p['color_images']) > 1 ? '' : ' loading="lazy"'; ?>>
            </a>
        <?php $first = false; endforeach; ?>
    </div>

    <aside class="az-product-buy" data-az-product data-name="<?php echo $this->escape($p['name']); ?>" data-price="<?php echo amuj_price($p['price']); ?>"
           data-img="<?php echo amuj_img($p['img']); ?>" data-az-variants="<?php echo $variantJson; ?>" data-az-cart="<?php echo $cartUrl; ?>">
        <h1><?php echo $this->escape($p['name']); ?></h1>
        <div class="az-price"><?php echo amuj_price($p['price']); ?></div>

        <?php if ($colorNames) : ?>
            <div class="az-small az-muted">رنگ: <span data-az-color-label><?php echo $colorNames[0]; ?></span></div>
            <div class="az-colors" data-az-choice="single">
                <?php foreach ($colorNames as $i => $name) : ?>
                    <button type="button" class="az-swatch<?php echo $i === 0 ? ' is-active' : ''; ?>" style="background:<?php echo $p['colors'][$i]; ?>"
                            data-az-color="<?php echo $i; ?>" data-value="<?php echo $colorIds[$i]; ?>" data-name="<?php echo $this->escape($name); ?>" aria-label="<?php echo $this->escape($name); ?>"></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <ul class="az-sizes" data-az-choice="single" data-az-sizes>
            <?php foreach ($p['sizes'] as $i => $size) :
                $sid = $p['size_ids'][$i] ?? 0;
                $out = $hasSizes && !($p['variants'][($colorIds[0] ?? 0) . '|' . $sid][1] ?? false); ?>
                <li class="<?php echo trim(($singleSize ? 'is-active ' : '') . ($out ? 'is-out' : '')); ?>" data-value="<?php echo $sid; ?>">
                    <span><?php echo $size; ?></span>
                    <span class="az-small" data-az-out<?php echo $out ? '' : ' hidden'; ?>>ناموجود</span>
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
// Size guide (cm), shared by all products. Placeholder measurements until the shop sends real ones.
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

<?php if ($related) : ?>
    <section class="az-section">
        <div class="az-section-head"><h2>ست کنید با</h2></div>
        <div class="az-row-slider" uk-slider="finite: true">
            <ul class="uk-slider-items">
                <?php foreach ($related as $rid => $rp) : ?>
                    <li><?php amuj_card($rid, $rp); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
<?php endif; ?>
