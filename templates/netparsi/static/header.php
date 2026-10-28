<?php
defined('_JEXEC') or die;

/**
 * Three-level menu: section > numbered group > links.
 * When going dynamic this maps 1:1 to a Joomla menu (mod_menu override):
 * level 1 = sections, level 2 = groups, level 3 = HikaShop category menu items.
 *
 * @var bool $amujOverlay
 */
$menuTree = [
    'فروشگاه' => [
        'جدیدترین‌ها'  => ['جدیدترین‌ها', 'پرفروش‌ها'],
        'ترندها'       => ['پاییز و زمستان ۱۴۰۵', 'مهمانی و مجلسی'],
        'دسته‌بندی‌ها' => array_merge(amuj_categories(), ['حراج']),
    ],
    'کالکشن‌ها' => [
        'فصلی'   => ['پاییز و زمستان ۱۴۰۵', 'بهار و تابستان ۱۴۰۵'],
        'استایل' => ['کالکشن رسمی', 'کالکشن روزمره', 'AMUJ STUDIO'],
    ],
    'حراج' => [
        'تخفیف‌ها' => ['تا ۳۰٪', 'تا ۵۰٪', 'آخرین سایزها'],
    ],
];
$menuLinks = ['درباره آموژ' => amuj_url('about'), 'راهنما' => amuj_url('help'), 'حساب کاربری' => amuj_url('account')];
$cartCount = amuj_fa(2);

$icoSearch = '<svg class="az-ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.5 15.5L21 21"/></svg>';
$icoUser   = '<svg class="az-ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/></svg>';
$icoClose  = '<svg viewBox="0 0 38 38" aria-hidden="true"><path d="M2 2l34 34M36 2L2 36"/></svg>';
?>
<header class="az-header<?php echo !empty($amujOverlay) ? ' az-header--overlay' : ''; ?>">
    <div class="az-header-start">
        <button class="az-burger" type="button" uk-toggle="target: #az-menu" aria-label="منو">
            <span></span><span></span>
        </button>
        <a class="az-logo" href="<?php echo amuj_url('home'); ?>" aria-label="گالری آموژ"><?php echo amuj_logo(); ?></a>
    </div>
    <nav class="az-header-end">
        <a class="az-search-trigger" href="<?php echo amuj_url('search'); ?>" data-az-search aria-label="جستجو"><span class="az-txt">جستجو</span><?php echo $icoSearch; ?></a>
        <a class="az-desk" href="<?php echo amuj_url('login'); ?>">ورود</a>
        <a class="az-desk" href="<?php echo amuj_url('help'); ?>">راهنما</a>
        <a class="az-cart-link" href="<?php echo amuj_url('cart'); ?>" aria-label="سبد خرید"><span class="az-txt">سبد (<?php echo $cartCount; ?>)</span><span class="az-bag"><?php echo $cartCount; ?></span></a>
    </nav>
</header>

<div id="az-menu" class="az-menu uk-modal-full" uk-modal>
    <div class="uk-modal-dialog uk-modal-body az-menu-dialog">
        <div class="az-menu-top">
            <button class="az-menu-close uk-modal-close" type="button" aria-label="بستن"><?php echo $icoClose; ?></button>
            <a class="az-logo" href="<?php echo amuj_url('home'); ?>" aria-label="گالری آموژ"><?php echo amuj_logo(); ?></a>
            <div class="az-menu-icons">
                <a href="<?php echo amuj_url('account'); ?>" aria-label="حساب کاربری"><?php echo $icoUser; ?></a>
                <a href="<?php echo amuj_url('search'); ?>" data-az-search aria-label="جستجو"><?php echo $icoSearch; ?></a>
                <a href="<?php echo amuj_url('cart'); ?>" aria-label="سبد خرید"><span class="az-bag"><?php echo $cartCount; ?></span></a>
            </div>
        </div>

        <div class="az-menu-body">
            <div>
                <ul class="az-menu-sections" uk-switcher="connect: #az-menu-panels; toggle: > li > a">
                    <?php foreach (array_keys($menuTree) as $section) : ?>
                        <li><a href="#"><?php echo $section; ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <ul class="az-menu-extra">
                    <?php foreach ($menuLinks as $label => $url) : ?>
                        <li><a href="<?php echo $url; ?>"><?php echo $label; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <ul id="az-menu-panels" class="uk-switcher az-menu-panels">
                <?php foreach ($menuTree as $groups) : ?>
                    <li>
                        <?php $n = 0; foreach ($groups as $group => $items) : $n++; ?>
                            <div class="az-menu-group">
                                <div class="az-menu-group-title">
                                    <span class="az-num">|<?php echo amuj_fa(str_pad($n, 2, '0', STR_PAD_LEFT)); ?>|</span>
                                    <?php echo $group; ?>
                                </div>
                                <ul class="az-menu-list">
                                    <?php foreach ($items as $item) :
                                        $classes = trim(($item === 'حراج' ? 'az-sale ' : '') . ((isset($_GET['cat']) && $_GET['cat'] === $item) ? 'is-active' : '')); ?>
                                        <li><a href="<?php echo amuj_url('category', ['cat' => $item]); ?>"<?php echo $classes ? ' class="' . $classes . '"' : ''; ?>><?php echo $item; ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <ul class="az-menu-extra az-menu-extra--mobile">
                <?php foreach ($menuLinks as $label => $url) : ?>
                    <li><a href="<?php echo $url; ?>"><?php echo $label; ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<?php
/*
 * Live search overlay. The JSON contract ({q, total, groups:[{key, items:[{title,url,image,price,meta}]}]})
 * matches mod_netparsisearch, so going dynamic only means pointing data-endpoint at
 * index.php?option=com_ajax&module=netparsisearch&method=search&format=json&id=MODULE_ID
 */
?>
<div class="az-search" id="az-search" aria-hidden="true" role="dialog" aria-label="جستجو"
     data-endpoint="<?php echo AMUJ_BASE; ?>index.php?amujsearch=1"
     data-min="2">
    <div class="az-search-top">
        <form class="az-search-form" action="<?php echo AMUJ_BASE; ?>index.php" method="get" role="search">
            <input type="hidden" name="page" value="search">
            <input class="az-search-input" type="search" name="q" placeholder="جستجوی مانتو، پالتو، رنگ یا جنس..." autocomplete="off" enterkeyhint="search" aria-label="عبارت جستجو">
            <span class="az-search-line"></span>
            <span class="az-search-progress"></span>
        </form>
        <button class="az-search-close" type="button" data-az-search-close aria-label="بستن"><?php echo $icoClose; ?></button>
    </div>
    <div class="az-search-body">
        <div class="az-search-intro">
            <div class="az-search-label">جستجوهای پرطرفدار</div>
            <div class="az-search-chips">
                <?php foreach (amuj_categories() as $i => $cat) : ?>
                    <button type="button" style="--i:<?php echo $i; ?>" data-az-term="<?php echo $cat; ?>"><?php echo $cat; ?></button>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="az-search-results" aria-live="polite"></div>
    </div>
</div>
