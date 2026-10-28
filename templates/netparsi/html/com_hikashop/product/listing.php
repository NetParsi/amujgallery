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

// Card data (images, price, colours / sizes from the variants)
$cards = amuj_hika_products(array_map(function ($r) { return (int) $r->product_id; }, $rows));
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
        <div class="az-cat-tools">
            <?php // Filter button + panel return with the HikaShop filter module (group 3 of the plan) ?>
            <div class="az-grid-switch" role="group" aria-label="تعداد ستون‌ها">
                <span class="az-grid-switch-label">نمایش</span>
                <?php foreach ([4, 5, 6] as $n) : ?>
                    <button type="button" class="az-desk-only" data-cols="<?php echo $n; ?>" aria-label="<?php echo amuj_fa($n); ?> ستون"><?php echo str_repeat('<i></i>', $n); ?></button>
                <?php endforeach; ?>
                <?php foreach ([1, 2] as $n) : ?>
                    <button type="button" class="az-mob-only" data-cols="<?php echo $n; ?>" aria-label="<?php echo amuj_fa($n); ?> ستون"><?php echo str_repeat('<i></i>', $n); ?></button>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>

    <div class="az-cat-main">
        <h1 class="az-cat-title"><?php echo $this->escape($title); ?> — <?php echo amuj_fa($total); ?> محصول</h1>

        <?php if ($rows) : ?>
            <div class="az-grid" id="az-grid">
                <?php foreach ($cards as $id => $card) {
                    amuj_card($id, $card);
                } ?>
            </div>

            <?php if ($limit > 0 && $start + $limit < $total) : ?>
                <div class="az-load-more">
                    <a class="az-btn az-btn--ghost" href="<?php echo Route::_('index.php?Itemid=' . (int) ($active->id ?? 0) . '&limitstart=' . ($start + $limit)); ?>">نمایش بیشتر</a>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div class="az-empty-cat">
                <p class="az-muted">فعلاً محصولی در این دسته نیست.</p>
                <a class="az-btn az-btn--ghost" href="<?php echo Uri::root(); ?>">بازگشت به صفحه اصلی</a>
            </div>
        <?php endif; ?>
    </div>
</div>

