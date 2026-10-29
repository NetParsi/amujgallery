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

// "Pair it with": the related products chosen in HikaShop (product > Related products), then other products of
// the same categories, then the newest ones
$db = Factory::getDbo();
$chosen = $db->setQuery('SELECT r.product_related_id FROM ' . hikashop_table('product_related') . ' r'
    . ' JOIN ' . hikashop_table('product') . ' p ON p.product_id = r.product_related_id AND p.product_published = 1 AND p.product_type = "main"'
    . ' WHERE r.product_id = ' . $mainId . ' AND r.product_related_type = "related" ORDER BY r.product_related_ordering')->loadColumn();
$related = $chosen;
if (count($related) < 8) {
    $related = array_merge($related, $db->setQuery('SELECT DISTINCT pc2.product_id FROM ' . hikashop_table('product_category') . ' pc'
        . ' JOIN ' . hikashop_table('product_category') . ' pc2 ON pc2.category_id = pc.category_id AND pc2.product_id != pc.product_id'
        . ' JOIN ' . hikashop_table('product') . ' p ON p.product_id = pc2.product_id AND p.product_published = 1 AND p.product_type = "main"'
        . ' WHERE pc.product_id = ' . $mainId . ' LIMIT 12')->loadColumn());
}
if (count(array_unique($related)) < 4) {
    $related = array_merge($related, $db->setQuery('SELECT product_id FROM ' . hikashop_table('product')
        . ' WHERE product_type = "main" AND product_published = 1 AND product_id != ' . $mainId . ' ORDER BY product_created DESC LIMIT 8')->loadColumn());
}
$related = amuj_hika_products(array_slice(array_values(array_diff(array_unique(array_map('intval', $related)), [$mainId])), 0, 8));
$relatedTitle = $chosen ? 'ست کنید با' : 'شاید بپسندید';

// Recently viewed: product ids in a cookie (newest first), this product added for the next pages
$cookie = Factory::getApplication()->getInput()->cookie;
$seen   = array_values(array_filter(array_map('intval', explode(',', (string) $cookie->getString('amuj_seen', '')))));
$cookie->set('amuj_seen', implode(',', array_slice(array_unique(array_merge([$mainId], $seen)), 0, 12)),
    ['expires' => time() + 60 * 86400, 'path' => Uri::root(true) ?: '/', 'samesite' => 'Lax']);
$recent = array_slice(array_values(array_diff($seen, [$mainId])), 0, 8);
$recent = $recent ? amuj_hika_products($recent) : [];

// Search engines and link previews: description, share image, Product + breadcrumb structured data
$app      = Factory::getApplication();
$menuItem = $app->getMenu()->getActive();
// One address per product for search engines: under the shop menu item of the product's own category
$canonItem = 0;
$prodCats  = $db->setQuery('SELECT category_id FROM ' . hikashop_table('product_category') . ' WHERE product_id = ' . $mainId . ' ORDER BY ordering')->loadColumn();
foreach ($app->getMenu()->getItems('menutype', 'amuj-main') as $item) {
    $hkp = (array) $item->getParams()->get('hk_product', []);
    if ($item->type === 'component' && in_array((int) ($hkp['selectparentlisting'] ?? 0), array_map('intval', $prodCats), true)) {
        $canonItem = (int) $item->id;
        break;
    }
}
$alias   = (string) $db->setQuery('SELECT product_alias FROM ' . hikashop_table('product') . ' WHERE product_id = ' . $mainId)->loadResult();
$pageUrl = amuj_seo_abs($canonItem
    ? Route::_('index.php?option=com_hikashop&ctrl=product&task=show&cid=' . $mainId . '&name=' . $alias . '&Itemid=' . $canonItem)
    : Route::_($p['url']));
$inStock  = (bool) array_filter($p['variants'], function ($v) { return !empty($v[1]); }) || !$p['variants'];
$images   = array_values(array_unique(array_map(function ($img) { return amuj_seo_abs(amuj_img($img)); }, $gallery ?: [$p['img']])));
$seoDesc  = amuj_seo_text($p['desc']) ?: amuj_seo_text($p['name'] . (count($colorNames) ? ' در رنگ‌های ' . implode('، ', $colorNames) : '')
    . ($hasSizes ? '، سایز ' . implode(' تا ', [reset($p['sizes']), end($p['sizes'])]) : '') . '. خرید آنلاین از گالری آموژ.');
$crumbs   = [['@type' => 'ListItem', 'position' => 1, 'name' => $app->get('sitename'), 'item' => rtrim(Uri::root(), '/') . '/']];
if ($menuItem && !$menuItem->home) {
    $crumbs[] = ['@type' => 'ListItem', 'position' => 2, 'name' => $menuItem->title, 'item' => amuj_seo_abs(Route::_('index.php?Itemid=' . $menuItem->id))];
}
$crumbs[] = ['@type' => 'ListItem', 'position' => count($crumbs) + 1, 'name' => $p['name'], 'item' => $pageUrl];
amuj_seo([
    'description' => $seoDesc,
    'image'       => $images[0] ?? '',
    'type'        => 'product',
    'canonical'   => $pageUrl,
    'og'          => ['product:price:amount' => (string) round($p['price'] * 10), 'product:price:currency' => 'IRR'],
    'schema'      => [
        '@type'       => 'Product',
        'name'        => $p['name'],
        'image'       => $images,
        'description' => $seoDesc,
        'sku'         => $p['code'],
        'brand'       => ['@type' => 'Brand', 'name' => $app->get('sitename')],
        'color'       => implode('، ', $colorNames),
        'offers'      => [
            '@type'         => 'Offer',
            'url'           => $pageUrl,
            // schema.org needs an ISO currency: the shop prices in toman, 1 toman = 10 rial
            'priceCurrency' => 'IRR',
            'price'         => (string) round($p['price'] * 10),
            'availability'  => $inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'itemCondition' => 'https://schema.org/NewCondition',
            'seller'        => ['@id' => rtrim(Uri::root(), '/') . '/#store'],
        ],
    ],
]);
amuj_seo(['schema' => ['@type' => 'BreadcrumbList', 'itemListElement' => $crumbs]]);
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
           data-img="<?php echo amuj_img($p['img']); ?>" data-az-variants="<?php echo $variantJson; ?>" data-az-cart="<?php echo $cartUrl; ?>"
           data-az-requested="<?php echo htmlspecialchars(json_encode(amuj_restock_requests())); ?>">
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
        <?php if ($hasSizes) : ?>
            <div class="az-notify-me" data-az-notify hidden>
                <p data-az-notify-text></p>
                <button class="az-btn az-btn--ghost az-btn--block" type="button" data-az-notify-btn>موجود شد خبرم کن</button>
                <small class="az-muted">وقتی موجود شد، پیامک و پیام در حساب کاربری‌تان دریافت می‌کنید.</small>
            </div>
        <?php endif; ?>
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
        <div class="az-policy-help">برای مشاوره سایز در واتس‌اپ پیام دهید: <a class="az-link" href="<?php echo amuj_contact()['whatsapp']; ?>" target="_blank" rel="noopener" dir="ltr"><?php echo amuj_contact()['phone']; ?></a></div>
    </div>
</div>

<?php if ($related) : ?>
    <section class="az-section">
        <div class="az-section-head"><h2><?php echo $relatedTitle; ?></h2></div>
        <div class="az-row-slider" uk-slider="finite: true">
            <ul class="uk-slider-items">
                <?php foreach ($related as $rid => $rp) : ?>
                    <li><?php amuj_card($rid, $rp); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
<?php endif; ?>

<?php if ($recent) : ?>
    <section class="az-section az-recent">
        <div class="az-section-head"><h2>اخیراً دیده‌اید</h2></div>
        <div class="az-row-slider" uk-slider="finite: true">
            <ul class="uk-slider-items">
                <?php foreach ($recent as $rid => $rp) : ?>
                    <li><?php amuj_card($rid, $rp); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
<?php endif; ?>
