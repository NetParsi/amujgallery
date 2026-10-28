<?php
defined('_JEXEC') or die;

/** @var bool $amujOverlay */
$menuGroups = [
    'زنانه'  => ['جدیدترین‌ها', 'پرفروش‌ها', 'کت و پالتو', 'بلیزر', 'پیراهن و شومیز', 'بافت', 'شلوار', 'جین', 'دامن', 'مانتو', 'کفش', 'کیف', 'اکسسوری', 'حراج'],
    'مردانه' => ['جدیدترین‌ها', 'کت و کاپشن', 'کت تک', 'پیراهن', 'تی‌شرت', 'سویشرت', 'شلوار', 'جین', 'کفش', 'اکسسوری', 'حراج'],
    'کودکان' => ['دختر ۶ تا ۱۴ سال', 'پسر ۶ تا ۱۴ سال', 'نوزاد دختر', 'نوزاد پسر', 'کفش', 'اکسسوری'],
];
?>
<header class="az-header<?php echo !empty($amujOverlay) ? ' az-header--overlay' : ''; ?>">
    <div class="az-header-start">
        <button class="az-burger" type="button" uk-toggle="target: #az-menu" aria-label="منو">
            <span></span><span></span>
        </button>
        <a class="az-logo" href="<?php echo amuj_url('home'); ?>">AMUJ</a>
    </div>
    <nav class="az-header-end">
        <a class="az-search-trigger" href="<?php echo amuj_url('search'); ?>">جستجو</a>
        <a class="uk-visible@s" href="<?php echo amuj_url('login'); ?>">ورود</a>
        <a class="uk-visible@s" href="<?php echo amuj_url('help'); ?>">راهنما</a>
        <a class="az-cart-link" href="<?php echo amuj_url('cart'); ?>">سبد <span>(<?php echo amuj_fa(2); ?>)</span></a>
    </nav>
</header>

<div id="az-menu" uk-offcanvas="overlay: true; mode: none">
    <div class="uk-offcanvas-bar az-menu">
        <button class="az-menu-close" type="button" uk-toggle="target: #az-menu" aria-label="بستن">
            <span uk-icon="icon: close; ratio: 1.1"></span>
        </button>
        <ul class="az-menu-tabs" uk-switcher="connect: #az-menu-panels; animation: uk-animation-fade">
            <?php foreach (array_keys($menuGroups) as $group) : ?>
                <li><a href="#"><?php echo $group; ?></a></li>
            <?php endforeach; ?>
        </ul>
        <ul id="az-menu-panels" class="uk-switcher">
            <?php foreach ($menuGroups as $items) : ?>
                <li>
                    <ul class="az-menu-list">
                        <?php foreach ($items as $i => $item) : ?>
                            <li><a href="<?php echo amuj_url('category'); ?>"<?php echo $item === 'حراج' ? ' class="az-sale"' : ''; ?>><?php echo $item; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endforeach; ?>
        </ul>
        <ul class="az-menu-foot">
            <li><a href="<?php echo amuj_url('account'); ?>">حساب کاربری</a></li>
            <li><a href="<?php echo amuj_url('help'); ?>">راهنما</a></li>
            <li><a href="<?php echo amuj_url('about'); ?>">درباره آموژ</a></li>
        </ul>
    </div>
</div>
