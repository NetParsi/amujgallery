<?php
/**
 * HikaShop product listing (category page) in the Amuj design.
 * Modules and AJAX requests keep HikaShop's own layout.
 *
 * Side column = the sibling links of the active item in the Joomla menu "amuj-main",
 * so the subcategory list is edited from the menu manager like the header menu.
 */
defined('_JEXEC') or die;

if ($this->module || !empty($this->tmpl_ajax)) {
    require JPATH_ROOT . '/components/com_hikashop/views/product/tmpl/listing.php';
    return;
}

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

$app    = Factory::getApplication();
$menu   = $app->getMenu();
$active = $menu->getActive();
$rows   = $this->rows ?: [];
$total  = (int) $this->pageInfo->elements->total;
$start  = (int) $this->pageInfo->limit->start;
$limit  = (int) $this->pageInfo->limit->value;
$title  = $active ? $active->title : ($this->element->category_name ?? '');
// HikaShop titles the page "product category": use the menu item / category name (unless the menu item sets a browser title)
if ($title !== '' && !($active && $active->getParams()->get('page_title'))) {
    Factory::getDocument()->setTitle($title . ' | ' . $app->get('sitename'));
}

// Sibling menu links (same group of the header menu)
$subnav = [];
if ($active && $active->level > 1) {
    foreach ($menu->getItems('parent_id', $active->parent_id) as $item) {
        if ($item->menutype !== $active->menutype || in_array($item->type, ['heading', 'separator'], true)) {
            continue;
        }
        $target   = $item->type === 'alias' ? (int) $item->getParams()->get('aliasoptions') : (int) $item->id;
        $subnav[] = ['title' => $item->title, 'url' => Route::_('index.php?Itemid=' . $target), 'active' => $target === (int) $active->id];
    }
}

// ---------------------------------------------------------------- filters (size, colour, price, sort)
// Every published product of the listing's category (and subcategories) with its variants; the options
// of the filter panel are built from them. Without a filter HikaShop's own page of products is shown.
$hk      = $active ? (array) $active->getParams()->get('hk_product', []) : [];
$catId   = (int) ($hk['selectparentlisting'] ?? ($this->element->category_id ?? 0));
$withSub = (int) ($hk['filter_type'] ?? 1) === 1;
$db      = Factory::getDbo();
$cats    = [$catId];
if ($catId && $withSub) {
    $cats = $db->setQuery('SELECT c.category_id FROM ' . hikashop_table('category') . ' c JOIN ' . hikashop_table('category') . ' p'
        . ' ON c.category_left BETWEEN p.category_left AND p.category_right WHERE p.category_id = ' . $catId . ' AND c.category_published = 1')->loadColumn();
}
$orders = ['product_created' => 'p.product_created DESC', 'product_sales' => 'p.product_sales DESC', 'product_name' => 'p.product_name ASC'];
$order  = $orders[$hk['product_order'] ?? ''] ?? 'MIN(pc.ordering) ASC';
$pool   = $cats ? $db->setQuery('SELECT p.product_id, p.product_created FROM ' . hikashop_table('product') . ' p'
    . ' JOIN ' . hikashop_table('product_category') . ' pc ON pc.product_id = p.product_id'
    . ' WHERE p.product_type = "main" AND p.product_published = 1 AND pc.category_id IN (' . implode(',', array_map('intval', $cats)) . ')'
    . ' GROUP BY p.product_id, p.product_created, p.product_sales, p.product_name ORDER BY ' . $order . ', p.product_id DESC')->loadAssocList('product_id', 'product_created') : [];
$all = $pool ? amuj_hika_products(array_keys($pool)) : [];

// Options of the panel
$optSizes = $optColors = [];
foreach ($all as $p) {
    foreach ($p['size_ids'] as $i => $sid) {
        $optSizes[$p['sizes'][$i]] = true;
    }
    foreach ($p['color_names'] as $i => $name) {
        $optColors[$name] = $p['colors'][$i];
    }
}
$optSizes = array_keys($optSizes);
usort($optSizes, function ($a, $b) { return is_numeric($a) && is_numeric($b) ? $a <=> $b : 0; });
$prices    = array_filter(array_column($all, 'price'));
$optPrices = [];
if (count($prices) > 1 && max($prices) > min($prices)) {
    $lo   = floor(min($prices) / 1e6) * 1e6;
    $step = max(1e6, ceil((max($prices) - $lo) / 4 / 1e6) * 1e6);
    for ($i = 0; $i < 4; $i++) {
        $a = $lo + $i * $step;
        $b = $i === 3 ? 0 : $a + $step;
        $label = !$i ? 'زیر ' . amuj_fa($b / 1e6) . ' میلیون'
            : (!$b ? 'بالای ' . amuj_fa($a / 1e6) . ' میلیون' : amuj_fa($a / 1e6) . ' تا ' . amuj_fa($b / 1e6) . ' میلیون');
        $key = (!$i ? 0 : $a) . '-' . $b;
        $has = array_filter($prices, function ($v) use ($a, $b, $i) { return ($i === 0 || $v >= $a) && (!$b || $v < $b); });
        if ($has) {
            $optPrices[$key] = $label;
        }
    }
}
$optSorts = ['new' => 'جدیدترین', 'cheap' => 'ارزان‌ترین', 'expensive' => 'گران‌ترین'];

// Chosen values (only values that exist as options)
$input    = $app->getInput();
$fSize    = array_values(array_intersect((array) $input->get('size', [], 'array'), $optSizes));
$fColor   = array_values(array_intersect((array) $input->get('color', [], 'array'), array_keys($optColors)));
$fPrice   = $input->getString('price', '');
$fPrice   = isset($optPrices[$fPrice]) ? $fPrice : '';
$fSort    = $input->getCmd('sort', '');
$fSort    = isset($optSorts[$fSort]) ? $fSort : '';
$filtered = $fSize || $fColor || $fPrice !== '' || $fSort;
$baseUrl  = $active ? Route::_('index.php?Itemid=' . (int) $active->id) : Uri::current();
$query    = array_filter(['size' => $fSize, 'color' => $fColor, 'price' => $fPrice, 'sort' => $fSort]);
$urlWith  = function (array $q) use ($baseUrl) {
    $q = array_filter($q);
    return $baseUrl . ($q ? (strpos($baseUrl, '?') === false ? '?' : '&') . http_build_query($q) : '');
};

if ($filtered) {
    [$pa, $pb] = $fPrice !== '' ? array_map('floatval', explode('-', $fPrice)) : [0, 0];
    $list = array_filter($all, function ($p) use ($fSize, $fColor, $fPrice, $pa, $pb) {
        if ($fColor && !array_intersect($p['color_names'], $fColor)) {
            return false;
        }
        if ($fSize) {
            // a variant in one of the sizes that is in stock (in one of the chosen colours, if any)
            $names = $p['size_ids'] ? array_combine($p['size_ids'], array_slice($p['sizes'], 0, count($p['size_ids']))) : [];
            $ok = false;
            foreach ($p['variants'] as $key => $v) {
                [$cid, $sid] = explode('|', $key);
                $ci = array_search((int) $cid, $p['color_ids']);
                $colorName = $ci !== false ? $p['color_names'][$ci] : '';
                if ($v[1] && in_array($names[(int) $sid] ?? '', $fSize, true) && (!$fColor || in_array($colorName, $fColor, true))) {
                    $ok = true;
                    break;
                }
            }
            if (!$ok) {
                return false;
            }
        }
        if ($fPrice !== '' && (($pa && $p['price'] < $pa) || ($pb && $p['price'] >= $pb))) {
            return false;
        }
        return true;
    });
    if ($fSort === 'new') {
        uksort($list, function ($a, $b) use ($pool) { return $pool[$b] <=> $pool[$a]; });
    } elseif ($fSort === 'cheap' || $fSort === 'expensive') {
        uasort($list, function ($a, $b) use ($fSort) { return $fSort === 'cheap' ? $a['price'] <=> $b['price'] : $b['price'] <=> $a['price']; });
    }
    $limit = $limit > 0 ? $limit : 24;
    $page  = max(1, $input->getInt('page', 1));
    $total = count($list);
    $cards = array_slice($list, 0, $page * $limit, true);
    $more  = $total > $page * $limit ? $urlWith($query + ['page' => $page + 1]) : '';
} else {
    // Card data (images, price, colours / sizes from the variants)
    $cards = amuj_hika_products(array_map(function ($r) { return (int) $r->product_id; }, $rows));
    $more  = $limit > 0 && $start + $limit < $total
        ? Route::_('index.php?Itemid=' . (int) ($active->id ?? 0) . '&limitstart=' . ($start + $limit)) : '';
}

// Chosen filters as removable chips
$chosen = [];
foreach ($fSize as $v) {
    $chosen[] = ['سایز ' . amuj_fa($v), $urlWith(['size' => array_values(array_diff($fSize, [$v]))] + $query)];
}
foreach ($fColor as $v) {
    $chosen[] = [$v, $urlWith(['color' => array_values(array_diff($fColor, [$v]))] + $query)];
}
if ($fPrice !== '') {
    $chosen[] = [$optPrices[$fPrice], $urlWith(['price' => ''] + $query)];
}
if ($fSort) {
    $chosen[] = [$optSorts[$fSort], $urlWith(['sort' => ''] + $query)];
}
$count = count($chosen);
?>
<div class="az-cat">
    <aside class="az-cat-side">
        <?php if ($subnav) : ?>
            <nav class="az-cat-nav" aria-label="زیردسته‌ها">
                <?php foreach ($subnav as $i => $item) : ?>
                    <a href="<?php echo $item['url']; ?>"<?php echo $item['active'] ? ' class="is-active" aria-current="page"' : ''; ?>>
                        <span class="az-num">|<?php echo amuj_fa(str_pad($i + 1, 2, '0', STR_PAD_LEFT)); ?>|</span><span class="az-cat-nav-label"><?php echo $this->escape($item['title']); ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
    </aside>

    <div class="az-cat-main">
        <div class="az-cat-head">
            <h1 class="az-cat-title"><?php echo $this->escape($title); ?> — <?php echo amuj_fa($total); ?> محصول</h1>
            <div class="az-cat-tools">
                <?php if ($all) : ?>
                    <button class="az-cat-filter<?php echo $count ? ' is-active' : ''; ?>" type="button" uk-toggle="target: #az-filter">فیلتر<?php echo $count ? ' (' . amuj_fa($count) . ')' : ''; ?></button>
                <?php endif; ?>
                <div class="az-grid-switch" role="group" aria-label="تعداد ستون‌ها">
                    <?php foreach ([4, 5, 6] as $n) : ?>
                        <button type="button" class="az-desk-only" data-cols="<?php echo $n; ?>" aria-label="<?php echo amuj_fa($n); ?> ستون"><?php echo str_repeat('<i></i>', $n); ?></button>
                    <?php endforeach; ?>
                    <?php foreach ([1, 2] as $n) : ?>
                        <button type="button" class="az-mob-only" data-cols="<?php echo $n; ?>" aria-label="<?php echo amuj_fa($n); ?> ستون"><?php echo str_repeat('<i></i>', $n); ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if ($chosen) : ?>
            <div class="az-cat-chosen">
                <?php foreach ($chosen as [$label, $url]) : ?>
                    <a href="<?php echo htmlspecialchars($url); ?>" aria-label="حذف فیلتر <?php echo $this->escape($label); ?>"><?php echo $this->escape($label); ?> <span aria-hidden="true">×</span></a>
                <?php endforeach; ?>
                <a class="az-cat-chosen-clear" href="<?php echo htmlspecialchars($baseUrl); ?>">پاک کردن همه</a>
            </div>
        <?php endif; ?>

        <?php if ($cards) : ?>
            <div class="az-grid" id="az-grid">
                <?php foreach ($cards as $id => $card) {
                    amuj_card($id, $card);
                } ?>
            </div>

            <?php if ($more) : ?>
                <div class="az-load-more">
                    <a class="az-btn az-btn--ghost" href="<?php echo htmlspecialchars($more); ?>">نمایش بیشتر</a>
                </div>
            <?php endif; ?>
        <?php elseif ($filtered) : ?>
            <div class="az-empty-cat">
                <p class="az-muted">محصولی با این فیلترها پیدا نشد.</p>
                <a class="az-btn az-btn--ghost" href="<?php echo htmlspecialchars($baseUrl); ?>">پاک کردن فیلترها</a>
            </div>
        <?php else : ?>
            <div class="az-empty-cat">
                <p class="az-muted">فعلاً محصولی در این دسته نیست.</p>
                <a class="az-btn az-btn--ghost" href="<?php echo Uri::root(); ?>">بازگشت به صفحه اصلی</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($all) : ?>
    <div id="az-filter" uk-offcanvas="overlay: true; flip: true">
        <form class="uk-offcanvas-bar az-filter-bar" method="get" action="<?php echo htmlspecialchars($baseUrl); ?>">
            <button class="uk-offcanvas-close" type="button" uk-close></button>
            <div class="az-title">فیلتر</div>

            <?php if ($optSizes) : ?>
                <h4>سایز</h4>
                <div class="az-chips">
                    <?php foreach ($optSizes as $v) : ?>
                        <label><input type="checkbox" name="size[]" value="<?php echo $this->escape($v); ?>"<?php echo in_array($v, $fSize, true) ? ' checked' : ''; ?>><span><?php echo amuj_fa($this->escape($v)); ?></span></label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($optColors) : ?>
                <h4>رنگ</h4>
                <div class="az-chips">
                    <?php foreach ($optColors as $name => $hex) : ?>
                        <label><input type="checkbox" name="color[]" value="<?php echo $this->escape($name); ?>"<?php echo in_array($name, $fColor, true) ? ' checked' : ''; ?>><span><i class="az-swatch" style="background:<?php echo $hex; ?>"></i><?php echo $this->escape($name); ?></span></label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($optPrices) : ?>
                <h4>قیمت</h4>
                <div class="az-chips">
                    <?php foreach ($optPrices as $key => $label) : ?>
                        <label><input type="radio" name="price" value="<?php echo $key; ?>"<?php echo $fPrice === $key ? ' checked' : ''; ?> data-az-uncheck><span><?php echo $label; ?></span></label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h4>مرتب‌سازی</h4>
            <div class="az-chips">
                <?php foreach ($optSorts as $key => $label) : ?>
                    <label><input type="radio" name="sort" value="<?php echo $key; ?>"<?php echo $fSort === $key ? ' checked' : ''; ?> data-az-uncheck><span><?php echo $label; ?></span></label>
                <?php endforeach; ?>
            </div>

            <div class="az-filter-actions">
                <a class="az-btn az-btn--ghost" href="<?php echo htmlspecialchars($baseUrl); ?>">پاک کردن</a>
                <button class="az-btn" type="submit">نمایش نتایج</button>
            </div>
        </form>
    </div>
<?php endif; ?>
