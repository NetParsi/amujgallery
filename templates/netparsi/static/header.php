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

/*
 * Menu images: shown beside the menu on desktop (follows hover) and as cards on mobile.
 * Dynamic: the "Link Image" field of each Joomla menu item.
 */
$sectionImages = ['فروشگاه' => 1, 'کالکشن‌ها' => 20, 'حراج' => 5];
$itemImages    = ['مانتو' => 4, 'کت' => 1, 'پالتو' => 8, 'بارانی' => 3, 'پیراهن' => 2, 'ست' => 5, 'شلوار' => 7,
    'دامن' => 6, 'شومیز' => 9, 'اکسسوری' => 10, 'جدیدترین‌ها' => 11, 'پرفروش‌ها' => 13, 'حراج' => 5];
$menuImg = function ($label) use ($itemImages) {
    return $itemImages[$label] ?? (abs(crc32($label)) % 22) + 1;
};
$menuPromos = [
    'فروشگاه'   => [['جدیدترین‌ها', 11], ['پالتو', 8]],
    'کالکشن‌ها' => [['پاییز و زمستان ۱۴۰۵', 20], ['AMUJ STUDIO', 19]],
    'حراج'      => [['تا ۵۰٪', 5], ['آخرین سایزها', 6]],
];

$icoSearch = '<svg class="az-ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.5 15.5L21 21"/></svg>';
$icoUser   = '<svg class="az-ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/></svg>';
$icoClose  = '<svg viewBox="0 0 38 38" aria-hidden="true"><path d="M2 2l34 34M36 2L2 36"/></svg>';
$icoUserKeep = str_replace('class="az-ico"', 'class="az-ico az-ico--keep"', $icoUser);
$amujUser    = amuj_current_user();
$amujNotes   = $amujUser ? amuj_notifications() : [];
$unreadCount = count(array_filter($amujNotes, function ($n) { return $n['unread']; }));

/*
 * Header slots are Joomla module positions (see templateDetails.xml). While a position
 * is empty the static demo markup is shown, so each slot can go dynamic on its own:
 * announcement = Custom module, header-user / header-cart = HikaShop user & cart modules.
 */
$hasModules = function ($position) {
    return isset($this) && method_exists($this, 'countModules') && $this->countModules($position);
};
$announcements = [
    'ارسال رایگان برای خریدهای بالای ' . amuj_fa(amuj_free_shipping() / 1000000) . ' میلیون تومان',
    'کالکشن پاییز و زمستان ۱۴۰۵ رسید',
    '۱۴ روز مهلت تعویض و مرجوعی',
];
?>
<a class="az-skip" href="#az-main">رفتن به محتوای اصلی</a>

<div class="az-topbar" id="az-topbar">
    <?php if ($hasModules('announcement')) : ?>
        <jdoc:include type="modules" name="announcement" style="none" />
    <?php else : ?>
        <div class="az-topbar-track" data-az-rotate>
            <?php foreach ($announcements as $i => $text) : ?>
                <span<?php echo $i === 0 ? ' class="is-active"' : ''; ?>><?php echo $text; ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <button class="az-topbar-close" type="button" aria-label="بستن نوار اطلاع‌رسانی">
        <svg viewBox="0 0 12 12" aria-hidden="true"><path d="M1 1l10 10M11 1L1 11"/></svg>
    </button>
</div>
<script>try { if (localStorage.getItem('azBarClosed')) document.documentElement.classList.add('az-no-bar'); } catch (e) {}</script>

<header class="az-header<?php echo !empty($amujOverlay) ? ' az-header--overlay' : ''; ?>">
    <div class="az-header-start">
        <button class="az-burger" type="button" uk-toggle="target: #az-menu" aria-label="منو">
            <span></span><span></span>
        </button>
        <a class="az-logo" href="<?php echo amuj_url('home'); ?>" aria-label="گالری آموژ"><?php echo amuj_logo(); ?></a>
    </div>
    <nav class="az-header-end" aria-label="دسترسی سریع">
        <a class="az-search-trigger" href="<?php echo amuj_url('search'); ?>" data-az-search aria-label="جستجو"><span class="az-txt">جستجو</span><?php echo $icoSearch; ?></a>
        <?php if ($hasModules('header-notifications')) : ?>
            <jdoc:include type="modules" name="header-notifications" style="none" />
        <?php elseif ($amujUser) : ?>
            <button class="az-bell" type="button" uk-toggle="target: #az-notify" aria-label="اعلان‌ها<?php echo $unreadCount ? '، ' . amuj_fa($unreadCount) . ' خوانده‌نشده' : ''; ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 17V11a6 6 0 0 1 12 0v6l1.5 2h-15z"/><path d="M10 21a2 2 0 0 0 4 0"/></svg>
                <?php if ($unreadCount) : ?><span class="az-bell-badge"><?php echo amuj_fa($unreadCount); ?></span><?php endif; ?>
            </button>
        <?php endif; ?>
        <?php if ($hasModules('header-user')) : ?>
            <jdoc:include type="modules" name="header-user" style="none" />
        <?php else : ?>
            <?php if ($amujUser) : ?>
                <button class="az-user-link is-signed" type="button" uk-toggle="target: #az-user" aria-label="حساب کاربری <?php echo $amujUser['first']; ?>"><?php echo $icoUserKeep; ?><span class="az-txt"><?php echo $amujUser['first']; ?></span></button>
            <?php else : ?>
                <a class="az-user-link" href="<?php echo amuj_url('login'); ?>" aria-label="ورود به حساب"><?php echo $icoUserKeep; ?><span class="az-txt">ورود</span></a>
            <?php endif; ?>
        <?php endif; ?>
        <a class="az-desk" href="<?php echo amuj_url('help'); ?>">راهنما</a>
        <?php if ($hasModules('header-cart')) : ?>
            <jdoc:include type="modules" name="header-cart" style="none" />
        <?php else : ?>
            <a class="az-cart-link" href="<?php echo amuj_url('cart'); ?>" aria-label="سبد خرید، <?php echo $cartCount; ?> کالا"><span class="az-txt">سبد</span><span class="az-bag"><?php echo $cartCount; ?></span></a>
        <?php endif; ?>
    </nav>
</header>

<div id="az-menu" class="az-menu uk-modal-full" uk-modal>
    <div class="uk-modal-dialog uk-modal-body az-menu-dialog">
        <div class="az-menu-top">
            <button class="az-menu-close uk-modal-close" type="button" aria-label="بستن"><?php echo $icoClose; ?></button>
            <a class="az-logo" href="<?php echo amuj_url('home'); ?>" aria-label="گالری آموژ"><?php echo amuj_logo(); ?></a>
            <div class="az-menu-icons">
                <?php if ($amujUser) : ?>
                    <a href="#az-user" class="is-signed" uk-toggle="target: #az-user" aria-label="حساب کاربری"><?php echo $icoUser; ?></a>
                <?php else : ?>
                    <a href="<?php echo amuj_url('login'); ?>" aria-label="ورود"><?php echo $icoUser; ?></a>
                <?php endif; ?>
                <a href="<?php echo amuj_url('search'); ?>" data-az-search aria-label="جستجو"><?php echo $icoSearch; ?></a>
                <a href="<?php echo amuj_url('cart'); ?>" aria-label="سبد خرید"><span class="az-bag"><?php echo $cartCount; ?></span></a>
            </div>
        </div>

        <div class="az-menu-body">
            <div>
                <ul class="az-menu-sections" uk-switcher="connect: #az-menu-panels; toggle: > li > a">
                    <?php foreach (array_keys($menuTree) as $section) : ?>
                        <li><a href="#" data-az-img="<?php echo $sectionImages[$section]; ?>"><?php echo $section; ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <ul class="az-menu-extra">
                    <?php foreach ($menuLinks as $label => $url) : ?>
                        <li><a href="<?php echo $url; ?>"><?php echo $label; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <ul id="az-menu-panels" class="uk-switcher az-menu-panels">
                <?php foreach ($menuTree as $section => $groups) : ?>
                    <li>
                        <div class="az-menu-promos">
                            <?php foreach ($menuPromos[$section] as [$label, $img]) : ?>
                                <a class="az-menu-promo" href="<?php echo amuj_url('category', ['cat' => $label]); ?>">
                                    <img src="<?php echo amuj_img($img, 500); ?>" alt="" loading="lazy">
                                    <span><?php echo $label; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <?php $n = 0; foreach ($groups as $group => $items) : $n++; ?>
                            <div class="az-menu-group">
                                <div class="az-menu-group-title">
                                    <span class="az-num">|<?php echo amuj_fa(str_pad($n, 2, '0', STR_PAD_LEFT)); ?>|</span>
                                    <?php echo $group; ?>
                                </div>
                                <ul class="az-menu-list<?php echo count($items) > 6 ? ' az-menu-list--cols' : ''; ?>">
                                    <?php foreach ($items as $item) :
                                        $classes = trim(($item === 'حراج' ? 'az-sale ' : '') . ((isset($_GET['cat']) && $_GET['cat'] === $item) ? 'is-active' : '')); ?>
                                        <li><a href="<?php echo amuj_url('category', ['cat' => $item]); ?>" data-az-img="<?php echo $menuImg($item); ?>"<?php echo $classes ? ' class="' . $classes . '"' : ''; ?>><?php echo $item; ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php
            // Desktop image panel: one layer per image, the hovered item's layer fades in
            $panelImages = array_unique(array_merge(array_values($sectionImages), array_values($itemImages)));
            foreach ($menuTree as $groups) {
                foreach ($groups as $items) {
                    foreach ($items as $item) {
                        $panelImages[] = $menuImg($item);
                    }
                }
            }
            ?>
            <div class="az-menu-visual" aria-hidden="true">
                <?php foreach (array_unique($panelImages) as $img) : ?>
                    <img src="<?php echo amuj_img($img, 900); ?>" data-az-layer="<?php echo $img; ?>" alt="" loading="lazy"<?php echo $img === $sectionImages['فروشگاه'] ? ' class="is-active"' : ''; ?>>
                <?php endforeach; ?>
            </div>

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

<?php if ($amujUser) :
    /*
     * Customer panel. Club level / wallet are sample values until the
     * custom customer-club extension is installed; it can render into the same markup.
     */
    $userLinks = [
        ['سفارش‌های من', amuj_url('orders'), ''],
        ['آدرس‌ها', amuj_url('addresses'), ''],
        ['کیف پول', amuj_url('credits'), amuj_price($amujUser['wallet'])],
        ['علاقه‌مندی‌ها', amuj_url('wishlist'), ''],
        ['پیام‌های من', amuj_url('messages'), $amujUser['unread'] ? amuj_fa($amujUser['unread']) . ' پیام جدید' : ''],
    ];
    ?>
<div id="az-user" uk-offcanvas="overlay: true; flip: true">
    <div class="uk-offcanvas-bar az-user-panel">
        <div class="az-user-top">
            <div>
                <div class="az-user-hello">سلام <?php echo $amujUser['first']; ?></div>
                <div class="az-user-phone" dir="ltr"><?php echo $amujUser['phone']; ?></div>
            </div>
            <button class="az-user-close uk-offcanvas-close" type="button" aria-label="بستن"><?php echo $icoClose; ?></button>
        </div>

        <div class="az-club">
            <div class="az-club-head">
                <span class="az-club-label">باشگاه مشتریان آموژ</span>
                <span class="az-club-level"><?php echo $amujUser['level']; ?></span>
            </div>
            <div class="az-club-bar" role="progressbar" aria-valuenow="<?php echo $amujUser['progress']; ?>" aria-valuemin="0" aria-valuemax="100">
                <span style="--p: <?php echo $amujUser['progress']; ?>%"></span>
            </div>
            <div class="az-club-meta">
                <span><?php echo amuj_price($amujUser['remaining']); ?> خرید تا سطح <strong><?php echo $amujUser['next_level']; ?></strong></span>
            </div>
        </div>

        <div class="az-wallet">
            <div>
                <span class="az-wallet-label">موجودی کیف پول</span>
                <strong class="az-wallet-amount"><?php echo amuj_price($amujUser['wallet']); ?></strong>
            </div>
        </div>

        <ul class="az-user-links">
            <?php foreach ($userLinks as $i => [$label, $url, $badge]) : ?>
                <li style="--i: <?php echo $i; ?>">
                    <a href="<?php echo $url; ?>">
                        <span><?php echo $label; ?></span>
                        <?php if ($badge) : ?><em><?php echo $badge; ?></em><?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="az-user-foot">
            <a class="az-btn az-btn--block" href="<?php echo amuj_url('account'); ?>">مشاهده حساب کاربری</a>
            <a class="az-user-logout" href="<?php echo amuj_url('home', ['demo_user' => 0]); ?>">خروج از حساب</a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($amujUser) : ?>
<div id="az-notify" uk-offcanvas="overlay: true; flip: true">
    <div class="uk-offcanvas-bar az-user-panel az-notify-panel">
        <div class="az-user-top">
            <div>
                <div class="az-user-hello">اعلان‌ها</div>
                <div class="az-user-phone" data-az-unread-label><?php echo $unreadCount ? amuj_fa($unreadCount) . ' پیام خوانده‌نشده' : 'همه پیام‌ها خوانده شده'; ?></div>
            </div>
            <button class="az-user-close uk-offcanvas-close" type="button" aria-label="بستن"><?php echo $icoClose; ?></button>
        </div>

        <?php if ($unreadCount) : ?>
            <button class="az-notify-readall" type="button" data-az-readall>علامت‌گذاری همه به‌عنوان خوانده‌شده</button>
        <?php endif; ?>

        <ul class="az-notify-list">
            <?php foreach ($amujNotes as $i => $note) : ?>
                <li class="az-note az-note--<?php echo $note['type']; ?><?php echo $note['unread'] ? ' is-unread' : ''; ?>" style="--i: <?php echo $i; ?>">
                    <div class="az-note-meta">
                        <span class="az-note-type"><?php echo $note['label']; ?></span>
                        <time><?php echo $note['time']; ?></time>
                    </div>
                    <strong class="az-note-title"><?php echo $note['title']; ?></strong>
                    <p class="az-note-text"><?php echo $note['text']; ?></p>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="az-user-foot">
            <a class="az-btn az-btn--block az-btn--ghost" href="<?php echo amuj_url('messages'); ?>">مشاهده همه پیام‌ها</a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Added-to-cart panel. Dynamic: HikaShop cart module (AJAX add-to-cart response). -->
<div id="az-added" uk-offcanvas="overlay: true; flip: true">
    <div class="uk-offcanvas-bar az-user-panel az-added-panel">
        <div class="az-user-top">
            <div class="az-user-hello">به سبد خرید اضافه شد</div>
            <button class="az-user-close uk-offcanvas-close" type="button" aria-label="بستن"><?php echo $icoClose; ?></button>
        </div>
        <div class="az-added-item">
            <img src="" alt="" data-az-added-img>
            <div>
                <strong data-az-added-name></strong>
                <span class="az-muted" data-az-added-meta></span>
                <span data-az-added-price></span>
            </div>
        </div>
        <div class="az-summary-row"><span>کالاهای سبد خرید</span><span data-az-added-count></span></div>
        <div class="az-user-foot">
            <a class="az-btn az-btn--block" href="<?php echo amuj_url('cart'); ?>">مشاهده سبد و تکمیل خرید</a>
            <button class="az-btn az-btn--block az-btn--ghost uk-offcanvas-close az-added-continue" type="button">ادامه خرید</button>
        </div>
    </div>
</div>
