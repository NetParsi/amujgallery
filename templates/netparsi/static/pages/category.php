<?php
defined('_JEXEC') or die;

$subnav   = array_merge(['مشاهده همه'], amuj_categories());
$current  = isset($_GET['cat']) && in_array($_GET['cat'], $subnav, true) ? $_GET['cat'] : 'مانتو';
$products = amuj_products();
// Repeat the demo set so the grid looks full
$list = array_merge(array_keys($products), array_keys($products));
?>
<?php
/*
 * Desktop: sticky side column (numbered subcategories, filter, grid density) beside the grid.
 * Mobile: one swipeable row of subcategories + filter / density bar.
 * Dynamic: the subcategory list = child categories of the current HikaShop category.
 */
?>
<div class="az-cat">
    <aside class="az-cat-side">
        <nav class="az-cat-nav" aria-label="زیردسته‌ها">
            <?php foreach ($subnav as $i => $item) : ?>
                <a href="<?php echo amuj_url('category', ['cat' => $item]); ?>"<?php echo $item === $current ? ' class="is-active" aria-current="page"' : ''; ?>>
                    <span class="az-num">|<?php echo amuj_fa(str_pad($i + 1, 2, '0', STR_PAD_LEFT)); ?>|</span><span class="az-cat-nav-label"><?php echo $item; ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="az-cat-tools">
            <button class="az-cat-filter" type="button" uk-toggle="target: #az-filter">فیلتر</button>
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
        <div class="az-cat-title"><?php echo $current; ?> — <?php echo amuj_fa(count($list)); ?> محصول</div>
        <div class="az-grid" id="az-grid">
            <?php foreach ($list as $id) : ?>
                <?php amuj_card($id, $products[$id]); ?>
            <?php endforeach; ?>
        </div>
        <div class="az-load-more">
            <button class="az-btn az-btn--ghost" type="button">نمایش بیشتر</button>
        </div>
    </div>
</div>

<div id="az-filter" uk-offcanvas="overlay: true; flip: true">
    <div class="uk-offcanvas-bar az-filter-bar">
        <button class="uk-offcanvas-close" type="button" uk-close></button>
        <div class="az-title">فیلتر</div>

        <h4>سایز</h4>
        <div class="az-chips" data-az-choice="multi">
            <?php foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $s) : ?><button type="button"><?php echo $s; ?></button><?php endforeach; ?>
        </div>

        <h4>رنگ</h4>
        <div class="az-chips" data-az-choice="multi">
            <?php foreach (['#000' => 'مشکی', '#fff' => 'سفید', '#c8b8a0' => 'بژ', '#6b4f3a' => 'قهوه‌ای', '#1f2a44' => 'سرمه‌ای', '#7a7a7a' => 'طوسی'] as $hex => $name) : ?>
                <button type="button"><span class="az-swatch" style="background:<?php echo $hex; ?>;width:10px;height:10px;vertical-align:middle"></span> <?php echo $name; ?></button>
            <?php endforeach; ?>
        </div>

        <h4>قیمت</h4>
        <div class="az-chips" data-az-choice="single">
            <button type="button">زیر ۱ میلیون</button>
            <button type="button">۱ تا ۲ میلیون</button>
            <button type="button">۲ تا ۴ میلیون</button>
            <button type="button">بالای ۴ میلیون</button>
        </div>

        <h4>مرتب‌سازی</h4>
        <div class="az-chips" data-az-choice="single">
            <button type="button" class="is-active">جدیدترین</button>
            <button type="button">ارزان‌ترین</button>
            <button type="button">گران‌ترین</button>
        </div>

        <div class="uk-margin-large-top uk-flex" style="gap:8px">
            <button class="az-btn az-btn--ghost uk-width-1-2" type="button">پاک کردن</button>
            <button class="az-btn uk-width-1-2 uk-offcanvas-close" type="button" style="position:static">فیلتر</button>
        </div>
    </div>
</div>
