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
        'دسته‌بندی‌ها' => amuj_categories(),
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

<div id="az-menu" class="az-menu uk-modal-full" uk-modal>
    <div class="uk-modal-dialog uk-modal-body az-menu-dialog">
        <div class="az-menu-top">
            <button class="az-menu-close uk-modal-close" type="button" aria-label="بستن">
                <svg width="38" height="38" viewBox="0 0 38 38"><path d="M2 2l34 34M36 2L2 36" stroke="#000" stroke-width="1.2"/></svg>
            </button>
            <a class="az-logo" href="<?php echo amuj_url('home'); ?>">AMUJ</a>
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
                                    <?php foreach ($items as $item) : ?>
                                        <li><a href="<?php echo amuj_url('category', ['cat' => $item]); ?>"<?php echo (isset($_GET['cat']) && $_GET['cat'] === $item) ? ' class="is-active"' : ''; ?>><?php echo $item; ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
