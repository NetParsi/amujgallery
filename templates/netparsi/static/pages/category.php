<?php
defined('_JEXEC') or die;

$subnav   = array_merge(['مشاهده همه'], amuj_categories());
$current  = isset($_GET['cat']) && in_array($_GET['cat'], $subnav, true) ? $_GET['cat'] : 'مانتو';
$products = amuj_products();
// Repeat the demo set so the grid looks full
$list = array_merge(array_keys($products), array_keys($products));
// Editorial tiles (two columns wide) inserted before these positions
$features = [
    4  => [3, 'کالکشن پاییز ۱۴۰۵', 'برش‌های بلند، رنگ‌های خنثی'],
    14 => [8, 'پالتوهای پشمی', 'گرم و مینیمال'],
];
?>
<div class="az-cat-head">
    <nav class="az-subnav">
        <?php foreach ($subnav as $item) : ?>
            <a href="<?php echo amuj_url('category', ['cat' => $item]); ?>"<?php echo $item === $current ? ' class="is-active"' : ''; ?>><?php echo $item; ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="az-toolbar">
        <button type="button" uk-toggle="target: #az-filter">فیلتر +</button>
        <div class="az-grid-switch" aria-label="نمایش">
            <button type="button" data-cols="2"><i></i><i></i></button>
            <button type="button" data-cols="4"><i></i><i></i><i></i><i></i></button>
            <button type="button" data-cols="6"><i></i><i></i><i></i><i></i><i></i><i></i></button>
        </div>
    </div>
</div>

<div class="az-cat-body">
    <div class="az-cat-title"><?php echo $current; ?> — <?php echo amuj_fa(count($list)); ?> محصول</div>
    <div class="az-grid cols-4" id="az-grid">
        <?php foreach ($list as $i => $id) : ?>
            <?php if (isset($features[$i])) : amuj_feature(...$features[$i]); endif; ?>
            <?php amuj_card($id, $products[$id]); ?>
        <?php endforeach; ?>
    </div>
    <div class="az-load-more">
        <button class="az-btn az-btn--ghost" type="button">نمایش بیشتر</button>
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
            <button class="az-btn uk-width-1-2 uk-offcanvas-close" type="button" style="position:static">مشاهده نتایج</button>
        </div>
    </div>
</div>
