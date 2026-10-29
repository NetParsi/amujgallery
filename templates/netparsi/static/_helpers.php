<?php

/**
 * Static demo layer for the netparsi template.
 *
 * Every static page lives in static/pages/{name}.php and is reachable with
 * index.php?page={name}. When a page becomes dynamic (HikaShop / com_content),
 * remove it from amuj_pages() and point the menu item to the real component.
 */

defined('_JEXEC') or die;

if (!function_exists('amuj_url')) {

    /** Page key => [title, header overlays the content (transparent header), bare page without header/footer] */
    function amuj_pages()
    {
        return [
            'home'     => ['گالری آموژ', true],
            'category' => ['مانتو | گالری آموژ', false],
            'product'  => ['جزئیات محصول', false],
            'search'   => ['جستجو', false],
            'cart'     => ['سبد خرید', false],
            'checkout' => ['تکمیل سفارش', false],
            'thankyou' => ['نتیجه پرداخت', false],
            'login'    => ['ورود / ثبت‌نام', false, true],
            'register' => ['ورود / ثبت‌نام', false, true],
            'account'  => ['داشبورد | حساب کاربری', false],
            'orders'   => ['سفارش‌های من', false],
            'order'    => ['جزئیات سفارش', false],
            'addresses'=> ['آدرس‌های من', false],
            'credits'  => ['کیف پول', false],
            'messages' => ['پیام‌های من', false],
            'wishlist' => ['علاقه‌مندی‌ها', false],
            'notfound' => ['صفحه پیدا نشد', false],
            'help'     => ['سؤالات متداول', false],
            'about'    => ['درباره ما', false],
            'contact'  => ['تماس با ما', false],
            'policy'   => ['قوانین و راهنما', false],
        ];
    }

    /**
     * Header menu from a Joomla menu: level 1 = sections, level 2 = groups, level 3 = links.
     * Returns [] outside Joomla or when the menu is missing, so the static tree is used instead.
     * Menu pictures come from each item's "Link Image"; the first two pictured links of a
     * section become its mobile promo cards.
     */
    function amuj_menu_tree($menutype)
    {
        if (!class_exists('\Joomla\CMS\Factory')) {
            return [];
        }

        $menu   = \Joomla\CMS\Factory::getApplication()->getMenu('site');
        $items  = $menu->getItems('menutype', $menutype);
        $active = $menu->getActive();
        $activeId = $active ? (int) $active->id : 0;

        if (!$items) {
            return [];
        }

        $image = function ($item) {
            $img = $item->getParams()->get('menu_image', '');
            // Joomla stores media field values as "path#joomlaImage://..."
            return $img ? strtok($img, '#') : '';
        };

        $sections = [];
        $groupRef = [];

        foreach ($items as $item) {
            if ($item->level == 1) {
                $sections[$item->id] = ['title' => $item->title, 'img' => $image($item), 'groups' => [], 'promos' => []];
            } elseif ($item->level == 2 && isset($sections[$item->parent_id])) {
                $sections[$item->parent_id]['groups'][$item->id] = ['title' => $item->title, 'items' => []];
                $groupRef[$item->id] = $item->parent_id;
            } elseif ($item->level == 3 && isset($groupRef[$item->parent_id])) {
                $target = $item->type === 'alias' ? (int) $item->getParams()->get('aliasoptions') : (int) $item->id;
                $url    = in_array($item->type, ['url', 'heading', 'separator'], true)
                    ? ($item->type === 'url' ? $item->link : '#')
                    : \Joomla\CMS\Router\Route::_('index.php?Itemid=' . $target);
                $img = $image($item);

                $sections[$groupRef[$item->parent_id]]['groups'][$item->parent_id]['items'][] = [
                    'title'  => $item->title,
                    'url'    => $url,
                    'img'    => $img ?: $sections[$groupRef[$item->parent_id]]['img'],
                    'active' => $target === $activeId,
                    'sale'   => in_array($item->alias, ['sale'], true),
                ];

                $section = &$sections[$groupRef[$item->parent_id]];
                if ($img && count($section['promos']) < 2 && $img !== $section['img']) {
                    $section['promos'][] = ['title' => $item->title, 'url' => $url, 'img' => $img];
                }
                unset($section);
            }
        }

        foreach ($sections as &$section) {
            $section['groups'] = array_values(array_filter($section['groups'], function ($g) { return $g['items']; }));
        }
        unset($section);

        return array_values(array_filter($sections, function ($s) { return $s['groups']; }));
    }

    /** Swatch colour of a HikaShop "رنگ" value */
    function amuj_color_hex($name)
    {
        $map = ['سفید' => '#f6f3ec', 'مشکی' => '#000', 'سبز' => '#bcd6b4', 'آبی' => '#a9c4e2', 'کرم' => '#efe6d4',
            'قهوه‌ای' => '#6b4f3a', 'بژ' => '#c8b8a0', 'سرمه‌ای' => '#1f2a44', 'طوسی' => '#8a8a8a', 'قرمز' => '#a3262a'];

        return $map[$name] ?? '#ccc';
    }

    /**
     * HikaShop products as card / product-page data, in the order of $ids.
     * Colours and sizes come from the characteristics with alias "color" and "size";
     * a colour's photo is the first image of its variants.
     */
    function amuj_hika_products(array $ids)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (!$ids || !function_exists('hikashop_table')) {
            return [];
        }

        $db     = \Joomla\CMS\Factory::getDbo();
        $in     = implode(',', $ids);
        $upload = trim(hikashop_config()->get('uploadfolder', 'images/com_hikashop/upload/'), '/') . '/';
        $itemid = (int) \Joomla\CMS\Factory::getApplication()->getInput()->getInt('Itemid', 0);

        $rows = $db->setQuery('SELECT product_id, product_name, product_alias, product_code, product_created, product_description, product_quantity'
            . ' FROM ' . hikashop_table('product') . " WHERE product_id IN ($in) AND product_published = 1")->loadObjectList('product_id');

        $prices = $db->setQuery('SELECT price_product_id, MIN(price_value) AS price FROM ' . hikashop_table('price')
            . " WHERE price_product_id IN ($in) GROUP BY price_product_id")->loadObjectList('price_product_id');

        $files = [];
        foreach ($db->setQuery('SELECT file_ref_id, file_path FROM ' . hikashop_table('file')
            . " WHERE file_type = 'product' AND file_ref_id IN ($in) ORDER BY file_ordering, file_id")->loadObjectList() as $f) {
            $files[$f->file_ref_id][] = $upload . $f->file_path;
        }

        $kinds = [];
        foreach ($db->setQuery('SELECT characteristic_id, characteristic_alias FROM ' . hikashop_table('characteristic')
            . ' WHERE characteristic_parent_id = 0')->loadObjectList() as $c) {
            $kinds[$c->characteristic_id] = $c->characteristic_alias;
        }

        $variantRows = $db->setQuery('SELECT p.product_id, p.product_parent_id, p.product_quantity, p.product_published,'
            . ' c.characteristic_id, c.characteristic_parent_id, c.characteristic_value, c.characteristic_ordering'
            . ' FROM ' . hikashop_table('product') . ' p'
            . ' JOIN ' . hikashop_table('variant') . ' v ON v.variant_product_id = p.product_id'
            . ' JOIN ' . hikashop_table('characteristic') . ' c ON c.characteristic_id = v.variant_characteristic_id'
            . " WHERE p.product_parent_id IN ($in) AND p.product_type = 'variant'"
            . ' ORDER BY c.characteristic_ordering')->loadObjectList();

        $defaults = [];
        foreach ($db->setQuery('SELECT v.variant_product_id, v.variant_characteristic_id FROM ' . hikashop_table('variant') . ' v'
            . ' JOIN ' . hikashop_table('characteristic') . ' c ON c.characteristic_id = v.variant_characteristic_id AND c.characteristic_parent_id > 0'
            . " WHERE v.variant_product_id IN ($in)")->loadObjectList() as $d) {
            $defaults[$d->variant_product_id][] = (int) $d->variant_characteristic_id;
        }

        $variants = [];
        $variantIds = [];
        foreach ($variantRows as $v) {
            $kind = $kinds[$v->characteristic_parent_id] ?? '';
            $variants[$v->product_parent_id][$v->product_id]['available'] = $v->product_published && (int) $v->product_quantity !== 0;
            $variants[$v->product_parent_id][$v->product_id][$kind] = [(int) $v->characteristic_id, $v->characteristic_value, (int) $v->characteristic_ordering];
            $variantIds[] = (int) $v->product_id;
        }
        $variantFiles = [];
        if ($variantIds) {
            foreach ($db->setQuery('SELECT file_ref_id, file_path FROM ' . hikashop_table('file')
                . " WHERE file_type = 'product' AND file_ref_id IN (" . implode(',', $variantIds) . ') ORDER BY file_ordering, file_id')->loadObjectList() as $f) {
                $variantFiles[$f->file_ref_id] = $variantFiles[$f->file_ref_id] ?? $upload . $f->file_path;
            }
        }

        $out = [];
        foreach ($ids as $id) {
            if (!isset($rows[$id])) {
                continue;
            }
            $r = $rows[$id];
            $images = $files[$id] ?? ['media/com_hikashop/images/barcode.png'];

            $colors = $sizes = $colorImages = $map = [];
            $sizeAvailable = [];
            foreach ($variants[$id] ?? [] as $vid => $v) {
                $c = $v['color'] ?? [0, '', 0];
                $s = $v['size'] ?? [0, '', 0];
                $colors[$c[0]] = [$c[1], $c[2]];
                $sizes[$s[0]]  = [$s[1], $s[2]];
                $map[$c[0] . '|' . $s[0]] = [$vid, $v['available']];
                $sizeAvailable[$s[0]] = ($sizeAvailable[$s[0]] ?? false) || $v['available'];
                if (isset($variantFiles[$vid]) && !isset($colorImages[$c[0]])) {
                    $colorImages[$c[0]] = $variantFiles[$vid];
                }
            }
            // The product's default colour (HikaShop variant tab) comes first
            $default = $defaults[$id] ?? [];
            uasort($colors, function ($a, $b) { return $a[1] <=> $b[1]; });
            foreach (array_keys($colors) as $cid) {
                if (in_array($cid, $default)) {
                    $colors = [$cid => $colors[$cid]] + $colors;
                    break;
                }
            }
            uasort($sizes, function ($a, $b) { return $a[1] <=> $b[1]; });
            $colors = array_map(function ($c) { return $c[0]; }, $colors);
            $sizes  = array_map(function ($s) { return $s[0]; }, $sizes);
            unset($colors[0], $sizes[0]);

            $out[$id] = [
                'id'           => $id,
                'name'         => $r->product_name,
                'code'         => $r->product_code,
                'desc'         => $r->product_description,
                'price'        => (float) ($prices[$id]->price ?? 0),
                'img'          => $images[0],
                'img2'         => $images[1] ?? $images[0],
                'images'       => $images,
                'tag'          => (int) $r->product_created > time() - 30 * 86400 ? 'جدید' : '',
                'url'          => hikashop_contentLink('product&task=show&cid=' . $id . '&name=' . $r->product_alias . ($itemid ? '&Itemid=' . $itemid : ''), $r),
                'color_ids'    => array_keys($colors),
                'color_names'  => array_values($colors),
                'colors'       => array_map('amuj_color_hex', array_values($colors)),
                'color_images' => $colorImages,
                'size_ids'     => array_keys($sizes),
                'sizes'        => $sizes ? array_values($sizes) : ['تک‌سایز'],
                'sizes_out'    => array_values(array_map(function ($sid) use ($sizes) { return $sizes[$sid]; },
                    array_keys(array_filter($sizeAvailable, function ($a, $sid) { return !$a && $sid; }, ARRAY_FILTER_USE_BOTH)))),
                'variants'     => $map,
            ];
        }

        return $out;
    }

    /** Store categories (women's clothing only) */
    function amuj_categories()
    {
        return ['مانتو', 'کت', 'پالتو', 'بارانی', 'پیراهن', 'ست', 'شلوار', 'دامن', 'شومیز', 'اکسسوری'];
    }

    /**
     * Signed-in customer, or null for guests.
     * Name comes from the Joomla session when someone is logged in; club data is sample
     * data until the custom customer-club extension is installed.
     * For demos: ?demo_user=1 signs in a sample user (remembered in a cookie), ?demo_user=0 signs out.
     */
    function amuj_current_user()
    {
        static $user = false;

        if ($user !== false) {
            return $user;
        }

        $name = null;

        if (class_exists('\Joomla\CMS\Factory')) {
            $joomlaUser = \Joomla\CMS\Factory::getApplication()->getIdentity();
            if ($joomlaUser && !$joomlaUser->guest) {
                $name = $joomlaUser->name;
            }
        }

        if ($name === null) {
            if (isset($_GET['demo_user'])) {
                $on = $_GET['demo_user'] === '1';
                if (!headers_sent()) {
                    setcookie('amuj_demo_user', $on ? '1' : '', $on ? time() + 86400 * 30 : time() - 3600, '/');
                }
            } else {
                // Account pages always show a signed-in sample customer in the demo
                $on = !empty($_COOKIE['amuj_demo_user']) || in_array($_GET['page'] ?? '', amuj_account_pages(), true);
            }
            $name = $on ? 'سارا محمدی' : null;
        }

        if ($name === null) {
            return $user = null;
        }

        $parts = preg_split('/\s+/u', trim($name));

        return $user = [
            'name'       => $name,
            'first'      => $parts[0],
            'phone'      => '۰۹۱۲ ***  ۴۵۶۷',
            'level'      => 'نقره‌ای',
            'next_level' => 'طلایی',
            'progress'   => 68,
            'remaining'  => 850000,
            'wallet'     => 1250000,
            'mobile'     => '۰۹۱۲۱۲۳۴۵۶۷',
            'email'      => 'sara.mohammadi@example.com',
            'birthday'   => '', // empty = not entered yet (dashboard shows the add button)
            'unread'     => 2,
        ];
    }

    function amuj_account_pages()
    {
        return ['account', 'orders', 'order', 'addresses', 'credits', 'messages', 'wishlist'];
    }

    /** Side menu shared by the text pages and the FAQ */
    function amuj_policy_menu($active)
    {
        $links = [
            'terms'    => ['شرایط و قوانین خرید', amuj_url('policy', ['doc' => 'terms'])],
            'shipping' => ['ارسال و تحویل', amuj_url('policy', ['doc' => 'shipping'])],
            'returns'  => ['تعویض و مرجوعی', amuj_url('policy', ['doc' => 'returns'])],
            'privacy'  => ['حریم خصوصی', amuj_url('policy', ['doc' => 'privacy'])],
            'faq'      => ['سؤالات متداول', amuj_url('help')],
            'contact'  => ['تماس با ما', amuj_url('contact')],
        ];
        echo '<nav class="az-account-nav az-policy-nav" aria-label="قوانین و راهنما">';
        foreach ($links as $key => [$label, $url]) {
            echo '<a href="' . $url . '"' . ($key === $active ? ' class="is-active" aria-current="page"' : '') . '>' . $label . '</a>';
        }
        echo '</nav>';
    }

    /** Solar Hijri month and year of a date, e.g. "آبان ۱۴۰۵" */
    function amuj_jalali_month($date)
    {
        $ts = is_numeric($date) ? (int) $date : strtotime((string) $date);
        [$gy, $gm, $gd] = array_map('intval', explode('-', date('Y-m-d', $ts)));
        $days = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        $gy2  = $gm > 2 ? $gy + 1 : $gy;
        $n    = 355666 + 365 * $gy + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100) + intdiv($gy2 + 399, 400) + $gd + $days[$gm - 1];
        $jy   = -1595 + 33 * intdiv($n, 12053);
        $n   %= 12053;
        $jy  += 4 * intdiv($n, 1461);
        $n   %= 1461;
        if ($n > 365) {
            $jy += intdiv($n - 1, 365);
            $n = ($n - 1) % 365;
        }
        $jm = $n < 186 ? 1 + intdiv($n, 31) : 7 + intdiv($n - 186, 30);
        $months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];

        return $months[$jm - 1] . ' ' . amuj_fa($jy);
    }

    /** Article text "<hN>heading</hN> ...body..." -> [[heading, body html], ...] (text before the first heading is skipped) */
    function amuj_split_headings($html, $tag = 'h2')
    {
        $parts = preg_split('#<' . $tag . '[^>]*>(.*?)</' . $tag . '>#is', (string) $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        $out = [];
        for ($i = 1; $i < count($parts); $i += 2) {
            $out[] = [trim(strip_tags($parts[$i])), trim($parts[$i + 1] ?? '')];
        }

        return $out;
    }

    /** Customer panel menu: page key => label */
    function amuj_account_menu()
    {
        return [
            'account'   => 'داشبورد',
            'orders'    => 'سفارش‌های من',
            'addresses' => 'آدرس‌ها',
            'credits'   => 'کیف پول',
            'wishlist'  => 'علاقه‌مندی‌ها',
            'messages'  => 'پیام‌های من',
        ];
    }

    /** Opens the shared customer-panel layout (side menu + content column) */
    function amuj_account_open($active)
    {
        $user   = amuj_current_user();
        $unread = count(array_filter(amuj_notifications(), function ($n) { return $n['unread']; }));
        ?>
        <div class="az-account">
            <aside class="az-account-side">
                <div class="az-account-hello">
                    <strong><?php echo $user['name']; ?></strong>
                    <span>سطح <?php echo $user['level']; ?></span>
                    <span class="az-account-phone" dir="ltr"><?php echo $user['mobile']; ?></span>
                </div>
                <nav class="az-account-nav" aria-label="حساب کاربری">
                    <?php foreach (amuj_account_menu() as $key => $label) : ?>
                        <a href="<?php echo amuj_url($key); ?>"<?php echo ($key === $active || ($active === 'order' && $key === 'orders')) ? ' class="is-active" aria-current="page"' : ''; ?>>
                            <?php echo $label; ?>
                            <?php if ($key === 'messages' && $unread) : ?><em><?php echo amuj_fa($unread); ?></em><?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                    <a class="az-account-logout" href="<?php echo amuj_url('home', ['demo_user' => 0]); ?>">خروج</a>
                </nav>
            </aside>
            <div class="az-account-main">
        <?php
    }

    function amuj_account_close()
    {
        echo '</div></div>';
    }

    /** Order statuses in sequence (sample). Dynamic: HikaShop order statuses. */
    function amuj_order_steps()
    {
        return ['ثبت سفارش', 'تأیید پرداخت', 'آماده‌سازی', 'تحویل به پست', 'تحویل شده'];
    }

    /** Customer orders (sample). step = index in amuj_order_steps(); -1 = cancelled */
    function amuj_orders()
    {
        return [
            'AM-140508-2731' => ['date' => '۸ آبان ۱۴۰۵', 'step' => 2, 'items' => [13 => ['۳۸', 'مشکی'], 2 => ['۳۸', 'کرم']],
                                 'shipping' => 85000, 'method' => 'پست پیشتاز', 'pay' => 'درگاه اینترنتی', 'tracking' => ''],
            'AM-140421-1984' => ['date' => '۲۱ مهر ۱۴۰۵', 'step' => 3, 'items' => [1 => ['۳۸', 'آبی'], 3 => ['۴۰', 'مشکی']],
                                 'shipping' => 85000, 'method' => 'پست پیشتاز', 'pay' => 'درگاه اینترنتی', 'tracking' => '۱۲۳۴۵۶۷۸۹۰۱۲۳۴۵۶۷۸۹۰۱۲'],
            'AM-140317-0442' => ['date' => '۱۷ شهریور ۱۴۰۵', 'step' => 4, 'items' => [4 => ['۳۸', 'قهوه‌ای']],
                                 'shipping' => 0, 'method' => 'تحویل حضوری', 'pay' => 'کیف پول', 'tracking' => ''],
            'AM-140302-0118' => ['date' => '۲ شهریور ۱۴۰۵', 'step' => -1, 'items' => [5 => ['۳۸', 'سفید']],
                                 'shipping' => 85000, 'method' => 'پست پیشتاز', 'pay' => 'درگاه اینترنتی', 'tracking' => ''],
        ];
    }

    function amuj_order_total(array $order)
    {
        $products = amuj_products();
        $total    = $order['shipping'];
        foreach (array_keys($order['items']) as $id) {
            $total += $products[$id]['price'];
        }
        return $total;
    }

    /** Status label + css modifier for an order */
    function amuj_order_status(array $order)
    {
        if ($order['step'] < 0) {
            return ['لغو شده', 'cancelled'];
        }
        $labels = ['در انتظار پرداخت', 'در حال پردازش', 'در حال آماده‌سازی', 'ارسال شده', 'تحویل شده'];
        return [$labels[$order['step']], $order['step'] >= 4 ? 'done' : ($order['step'] === 3 ? 'shipped' : 'active')];
    }

    /** Wallet history (sample). Dynamic: the customer-club extension. */
    function amuj_transactions()
    {
        return [
            ['date' => '۸ آبان ۱۴۰۵', 'title' => 'هدیه ارتقای سطح به نقره‌ای', 'type' => 'wallet', 'amount' => 300000],
            ['date' => '۲۱ مهر ۱۴۰۵', 'title' => 'پرداخت بخشی از سفارش AM-140421-1984', 'type' => 'wallet', 'amount' => -450000],
            ['date' => '۱۵ مهر ۱۴۰۵', 'title' => 'بازگشت وجه سفارش لغوشده AM-140302-0118', 'type' => 'wallet', 'amount' => 1590000],
            ['date' => '۱۷ شهریور ۱۴۰۵', 'title' => 'پرداخت سفارش AM-140317-0442', 'type' => 'wallet', 'amount' => -2290000],
            ['date' => '۱ شهریور ۱۴۰۵', 'title' => 'هدیه تولد', 'type' => 'wallet', 'amount' => 150000],
        ];
    }

    /** Saved addresses (sample). Dynamic: HikaShop addresses. */
    function amuj_addresses()
    {
        return [
            ['label' => 'خانه', 'name' => 'سارا محمدی', 'phone' => '۰۹۱۲۱۲۳۴۵۶۷', 'province' => 'تهران', 'city' => 'تهران',
             'address' => 'ولنجک، خیابان چهاردهم، پلاک ۱۹، برج هوما، طبقه ۹، واحد ۹۰۴', 'postal' => '۱۹۸۴۷۱۵۶۳۲', 'default' => true],
            ['label' => 'محل کار', 'name' => 'سارا محمدی', 'phone' => '۰۹۱۲۱۲۳۴۵۶۷', 'province' => 'تهران', 'city' => 'تهران',
             'address' => 'خیابان ولیعصر، بالاتر از پارک ساعی، کوچه نیلوفر، پلاک ۸، طبقه ۳', 'postal' => '۱۵۱۱۷۴۴۳۱۱', 'default' => false],
        ];
    }

    /**
     * Customer notifications (sample). Automatic ones come from the customer-club
     * extension (credit added, level up); manual ones are sent by the shop.
     */
    function amuj_notifications()
    {
        return [
            ['type' => 'credit', 'label' => 'کیف پول', 'title' => '۳۰۰٬۰۰۰ تومان به کیف پول شما اضافه شد',
             'text' => 'هدیه ارتقای سطح شما به «نقره‌ای». این مبلغ در پرداخت سفارش‌ها قابل استفاده است.', 'time' => '۲ ساعت پیش', 'unread' => true],
            ['type' => 'level', 'label' => 'باشگاه مشتریان', 'title' => 'تبریک! سطح شما به «نقره‌ای» ارتقا یافت',
             'text' => 'از این پس از ارسال رایگان و پیش‌خرید کالکشن‌های جدید بهره‌مند می‌شوید.', 'time' => 'دیروز', 'unread' => true],
            ['type' => 'manual', 'label' => 'گالری آموژ', 'title' => 'کالکشن پاییز ۱۴۰۵ برای اعضای باشگاه زودتر در دسترس است',
             'text' => 'تا پایان هفته، پیش از عرضه عمومی خرید کنید.', 'time' => '۳ روز پیش', 'unread' => false],
            ['type' => 'credit', 'label' => 'کیف پول', 'title' => '۱۵۰٬۰۰۰ تومان هدیه تولد به کیف پول شما اضافه شد',
             'text' => 'تولدتان مبارک! این هدیه کوچک از طرف آموژ است.', 'time' => '۲ هفته پیش', 'unread' => false],
        ];
    }

    /** Inline brand logo (images/amuj-logo.svg) so it can follow currentColor */
    function amuj_logo()
    {
        static $svg = null;

        if ($svg === null) {
            $file = AMUJ_ROOT . '/images/amuj-logo.svg';
            $svg  = is_file($file) ? file_get_contents($file) : '';
            $svg  = preg_replace(['/<\?xml.*?\?>/s', '/<!--.*?-->/s', '/<defs>.*?<\/defs>/s', '/\s(id|class)="[^"]*"/'], '', $svg);
            $svg  = str_replace('<svg ', '<svg role="img" aria-label="گالری آموژ" fill="currentColor" fill-rule="evenodd" ', trim($svg));
        }

        return $svg ?: 'AMUJ';
    }

    /** Orders at or above this amount (toman) ship free. HikaShop: shipping method rule. */
    function amuj_free_shipping()
    {
        return (int) amuj_var('shop_free_shipping', 20000000);
    }

    /**
     * Site-wide value from the "Global variables" component (com_globalvars, admin: Components > متغیرهای سراسری).
     * Missing, unpublished or empty -> $default.
     */
    function amuj_var($key, $default = null)
    {
        static $ready = null;

        if ($ready === null) {
            $file  = defined('JPATH_ADMINISTRATOR') ? JPATH_ADMINISTRATOR . '/components/com_globalvars/helpers/globalvars.php' : '';
            $ready = $file && is_file($file);
            if ($ready) {
                require_once $file;
            }
        }
        if (!$ready) {
            return $default;
        }

        $value = \GlobalVarsHelper::get($key);

        return ($value === null || $value === '' || $value === []) ? $default : $value;
    }

    /** Shop contact details (global variables group "اطلاعات تماس"), ready for links */
    function amuj_contact()
    {
        static $contact = null;

        if ($contact !== null) {
            return $contact;
        }

        $mobile = strtr(trim(strtok((string) amuj_var('contact_mobile', '09354466989'), "\n")), array_flip(['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹']));
        $mobile = preg_replace('/\D/', '', $mobile);
        $intl   = '98' . ltrim($mobile, '0');

        // social_networks rows: title / code (link) / icon (instagram, whatsapp, telegram, ...)
        $social = [];
        foreach ((array) amuj_var('social_networks', []) as $row) {
            if (!empty($row['code'])) {
                $social[strtolower(trim($row['icon'] ?? '')) ?: ($row['title'] ?? '')] = ['title' => $row['title'] ?? '', 'url' => trim($row['code'])];
            }
        }
        $instagram = $social['instagram']['url'] ?? 'https://www.instagram.com/amuj.gallery';

        return $contact = [
            'phone'     => amuj_fa($mobile),
            'tel'       => '+' . $intl,
            'whatsapp'  => $social['whatsapp']['url'] ?? 'https://wa.me/' . $intl,
            'instagram' => $instagram,
            'instagram_handle' => '@' . trim(parse_url($instagram, PHP_URL_PATH) ?: '', '/'),
            'address'   => trim((string) amuj_var('contact_address', 'تهران، ولنجک، خیابان چهاردهم، پلاک ۱۹، برج هوما، طبقه ۹، واحد ۹۰۴')),
            'map'       => trim((string) amuj_var('contact_map_query', 'برج هوما ولنجک تهران')),
            'email'     => trim((string) amuj_var('contact_email', '')),
            'social'    => $social,
        ];
    }

    /**
     * Shipping cost by province (sample). Dynamic: HikaShop shipping methods with zone restrictions.
     * Returns [province => [method, cost]].
     */
    function amuj_shipping_rates()
    {
        return [
            'تهران'       => ['پیک فوری (همان روز)', 95000],
            'البرز'       => ['پست پیشتاز (۱ تا ۲ روز)', 85000],
            'اصفهان'      => ['پست پیشتاز (۲ تا ۳ روز)', 110000],
            'فارس'        => ['پست پیشتاز (۲ تا ۴ روز)', 120000],
            'خراسان رضوی' => ['پست پیشتاز (۲ تا ۴ روز)', 125000],
            'سایر استان‌ها' => ['پست پیشتاز (۳ تا ۵ روز)', 140000],
        ];
    }

    /**
     * Page URL. A page that has gone dynamic has a menu item with the same alias in the
     * hidden menu "amuj-pages" (e.g. cart); that item wins over the static demo page.
     * login / register go to the first step (mobile) of the SMS auth component's menu items.
     */
    function amuj_url($page = 'home', array $query = [])
    {
        static $dynamic = null;

        if ($dynamic === null) {
            $dynamic = [];
            if (class_exists('\Joomla\CMS\Factory')) {
                $menu = \Joomla\CMS\Factory::getApplication()->getMenu('site');
                foreach ($menu->getItems('menutype', 'amuj-pages') as $item) {
                    $dynamic[$item->alias] = (int) $item->id;
                }
                foreach ($menu->getItems('link', 'index.php?option=com_auth&view=cellphone') as $item) {
                    $dynamic += ['login' => (int) $item->id, 'register' => (int) $item->id];
                }
            }
        }

        // Shop pages: category&cat=کت -> the header-menu item with that title; plain "category" -> newest products
        if ($page === 'category' && class_exists('\Joomla\CMS\Factory')) {
            static $shop = null;
            if ($shop === null) {
                $shop = [];
                foreach (\Joomla\CMS\Factory::getApplication()->getMenu('site')->getItems('menutype', 'amuj-main') as $item) {
                    if ($item->type === 'component' && !isset($shop[$item->title])) {
                        $shop[$item->title] = (int) $item->id;
                        $shop[$item->alias] = $shop[$item->alias] ?? (int) $item->id;
                    }
                }
            }
            $key = isset($query['cat']) ? $query['cat'] : 'new-in';
            if (isset($shop[$key])) {
                return \Joomla\CMS\Router\Route::_('index.php?Itemid=' . $shop[$key]);
            }
        }

        // Text pages: policy&doc=terms -> the "terms" article page
        if ($page === 'policy' && isset($query['doc'], $dynamic[$query['doc']])) {
            $page = $query['doc'];
            unset($query['doc']);
        }

        if (isset($dynamic[$page])) {
            return \Joomla\CMS\Router\Route::_('index.php?Itemid=' . $dynamic[$page] . ($query ? '&' . http_build_query($query) : ''));
        }

        return AMUJ_BASE . 'index.php?' . http_build_query(['page' => $page] + $query);
    }

    /**
     * Shop page of a HikaShop category: the "amuj-main" menu item listing it (keeps the header menu
     * and side navigation), else $fallback (e.g. the link a module built).
     */
    function amuj_category_url($catId, $fallback = '')
    {
        static $map = null;

        if ($map === null) {
            $map = [];
            if (class_exists('\Joomla\CMS\Factory')) {
                foreach (\Joomla\CMS\Factory::getApplication()->getMenu('site')->getItems('menutype', 'amuj-main') as $item) {
                    $hk  = (array) $item->getParams()->get('hk_product', []);
                    $cat = (int) ($hk['selectparentlisting'] ?? 0);
                    if ($item->type === 'component' && $cat && !isset($map[$cat])) {
                        $map[$cat] = \Joomla\CMS\Router\Route::_('index.php?Itemid=' . $item->id);
                    }
                }
            }
        }

        return $map[(int) $catId] ?? $fallback;
    }

    /** Sign-in page that brings the visitor back to the current page afterwards */
    function amuj_login_url()
    {
        if (!class_exists('\Joomla\CMS\Factory')) {
            return amuj_url('login');
        }
        if (\Joomla\CMS\Factory::getApplication()->getInput()->getCmd('option') === 'com_auth') {
            return amuj_url('login');
        }

        return amuj_url('login', ['return' => base64_encode(\Joomla\CMS\Uri\Uri::getInstance()->toString())]);
    }

    /** Sign-out link: Joomla logout for a real customer, the demo switch otherwise */
    function amuj_logout_url()
    {
        if (class_exists('\Joomla\CMS\Factory') && !\Joomla\CMS\Factory::getApplication()->getIdentity()->guest) {
            return \Joomla\CMS\Router\Route::_('index.php?option=com_users&task=user.logout&'
                . \Joomla\CMS\Session\Session::getFormToken() . '=1&return=' . base64_encode(\Joomla\CMS\Uri\Uri::root()));
        }

        return amuj_url('home', ['demo_user' => 0]);
    }

    /** Number of items in the visitor's HikaShop cart (static demo: 2) */
    function amuj_cart_count()
    {
        static $count = null;

        if ($count !== null) {
            return $count;
        }
        if (!function_exists('hikashop_get') && is_file(JPATH_ADMINISTRATOR . '/components/com_hikashop/helpers/helper.php')) {
            include_once JPATH_ADMINISTRATOR . '/components/com_hikashop/helpers/helper.php';
        }
        if (!function_exists('hikashop_get')) {
            return $count = 2;
        }

        $cartClass = hikashop_get('class.cart');
        $cartId    = $cartClass->getCurrentCartId('cart');
        $count     = 0;
        if ($cartId) {
            $count = (int) \Joomla\CMS\Factory::getDbo()->setQuery('SELECT SUM(cart_product_quantity) FROM ' . hikashop_table('cart_product')
                . ' WHERE cart_id = ' . (int) $cartId . ' AND cart_product_option_parent_id = 0')->loadResult();
        }

        return $count;
    }

    function amuj_fa($value)
    {
        return strtr((string) $value, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹', ',' => '٬']);
    }

    function amuj_price($amount)
    {
        return amuj_fa(number_format($amount)) . ' تومان';
    }

    /**
     * Image URL. A path is used as is; a number picks one of the shop photos
     * (or /images/demo/{n}.jpg when that file exists).
     */
    function amuj_img($n, $width = 900)
    {
        // A real image path (e.g. images/demo-product/x.jpg) is used as is
        if (!is_numeric($n)) {
            return AMUJ_BASE . ltrim($n, '/');
        }

        $local = 'images/demo/' . $n . '.jpg';

        if (is_file(AMUJ_ROOT . '/' . $local)) {
            return AMUJ_BASE . $local;
        }

        // Real shop photos; numbered placeholders anywhere in the theme cycle through them
        $photos = [
            'images/products/cream/1.jpg', 'images/products/bomber/1.jpg', 'images/products/monogram/black.jpg',
            'images/products/cream/2.jpg', 'images/products/suede/1.jpg', 'images/products/bomber/2.jpg',
            'images/products/monogram/brown.jpg', 'images/products/cream/3.jpg', 'images/products/bomber/3.jpg',
            'images/products/suede/2.jpg', 'images/products/art/1.jpg', 'images/demo-product/harir-black.jpg',
            'images/demo-product/harir-green.jpg', 'images/demo-product/harir-white.jpg',
        ];

        return AMUJ_BASE . $photos[(max(1, (int) $n) - 1) % count($photos)];
    }

    function amuj_products()
    {
        $black = '#000'; $beige = '#c8b8a0'; $brown = '#6b4f3a'; $navy = '#1f2a44'; $cream = '#efe9df'; $gray = '#8a8a8a';

        $harir = 'images/demo-product/harir-';

        return [
            // Real product for the presentation (first everywhere)
            13 => ['name' => 'پیراهن حریر', 'cat' => 'پیراهن', 'price' => 15500000,
                   'img' => $harir . 'black.jpg', 'img2' => $harir . 'green.jpg', 'tag' => 'جدید',
                   'colors'       => ['#f6f3ec', $black, '#bcd6b4'],
                   'color_names'  => ['سفید', 'مشکی', 'سبز'],
                   'color_images' => [$harir . 'white.jpg', $harir . 'black.jpg', $harir . 'green.jpg'],
                   'active_color' => 1,
                   'sizes'        => ['۳۶', '۳۸', '۴۰', '۴۲'],
                   'desc'         => 'پیراهن حریر سبک و روان با رُفل‌های لایه‌ای در دامن، گل‌های پارچه‌ای روی یقه و آستین پفی با مچ کش‌دار. در سایزهای ۳۶ تا ۴۲.',
                   'details'      => ['جنس: حریر', 'قد: ۸۰ سانتی‌متر', 'سایزها: ۳۶، ۳۸، ۴۰، ۴۲', 'رنگ‌بندی: سفید، مشکی، سبز'],
                   'code'         => 'AM-1405-013'],
            // Real products from the shop's photos. Prices are placeholders until the shop sends them.
            1 => ['name' => 'کت بامبر راه‌راه', 'cat' => 'کت', 'price' => 8900000,
                  'img' => 'images/products/bomber/1.jpg', 'img2' => 'images/products/bomber/2.jpg', 'tag' => 'جدید',
                  'colors' => ['#a9c4e2'], 'color_names' => ['آبی'],
                  'gallery' => ['images/products/bomber/1.jpg', 'images/products/bomber/2.jpg', 'images/products/bomber/3.jpg'],
                  'sizes' => ['۳۶', '۳۸', '۴۰', '۴۲'],
                  'desc' => 'کت بامبر سبک با راه‌راه آبی و سفید، یقه و مچ کشی، زیپ فلزی و آرم دوزی‌شده روی سینه و آستین.',
                  'details' => ['مدل: بامبر', 'رنگ: آبی و سفید', 'سایزها: ۳۶، ۳۸، ۴۰، ۴۲'], 'code' => 'AM-1405-001'],
            2 => ['name' => 'کت کرم زیپ‌دار', 'cat' => 'کت', 'price' => 9600000,
                  'img' => 'images/products/cream/1.jpg', 'img2' => 'images/products/cream/2.jpg', 'tag' => 'جدید',
                  'colors' => ['#efe6d4'], 'color_names' => ['کرم'],
                  'gallery' => ['images/products/cream/1.jpg', 'images/products/cream/2.jpg', 'images/products/cream/3.jpg'],
                  'sizes' => ['۳۶', '۳۸', '۴۰', '۴۲'],
                  'desc' => 'کت کوتاه کرم با یقه پیراهنی، جیب‌های زیپ‌دار طلایی و زیپ روی آستین. کالکشن بهار ۲۰۲۶.',
                  'details' => ['مدل: کت کوتاه', 'رنگ: کرم', 'یراق‌آلات طلایی', 'سایزها: ۳۶، ۳۸، ۴۰، ۴۲'], 'code' => 'AM-1405-002'],
            3 => ['name' => 'کت مونوگرام دورنگ', 'cat' => 'کت', 'price' => 11800000,
                  'img' => 'images/products/monogram/black.jpg', 'img2' => 'images/products/monogram/brown.jpg', 'tag' => 'پرفروش',
                  'colors' => ['#2a2724', '#6b4f3a'], 'color_names' => ['مشکی', 'قهوه‌ای'],
                  'color_images' => ['images/products/monogram/black.jpg', 'images/products/monogram/brown.jpg'],
                  'sizes' => ['۳۶', '۳۸', '۴۰', '۴۲'],
                  'desc' => 'کت چرم و جیر با طرح مونوگرام، یقه چرمی و مچ طرح‌دار. کالکشن پاییز و زمستان ۲۰۲۶.',
                  'details' => ['مدل: کت کوتاه', 'رنگ‌بندی: مشکی، قهوه‌ای', 'سایزها: ۳۶، ۳۸، ۴۰، ۴۲'], 'code' => 'AM-1405-003'],
            4 => ['name' => 'کت جیر مونوگرام', 'cat' => 'کت', 'price' => 12400000,
                  'img' => 'images/products/suede/1.jpg', 'img2' => 'images/products/suede/2.jpg', 'tag' => '',
                  'colors' => ['#8a5a3c'], 'color_names' => ['قهوه‌ای'],
                  'gallery' => ['images/products/suede/1.jpg', 'images/products/suede/2.jpg'],
                  'sizes' => ['۳۶', '۳۸', '۴۰', '۴۲'],
                  'desc' => 'کت جیر قهوه‌ای با طرح مونوگرام روی سینه، یقه چرم و جیب‌های دکمه‌دار.',
                  'details' => ['مدل: کت جیر', 'رنگ: قهوه‌ای', 'سایزها: ۳۶، ۳۸، ۴۰، ۴۲'], 'code' => 'AM-1405-004'],
            5 => ['name' => 'کت سفید طرح‌دار', 'cat' => 'کت', 'price' => 8400000,
                  'img' => 'images/products/art/1.jpg', 'img2' => 'images/products/art/1.jpg', 'tag' => '',
                  'colors' => ['#f4f1ea'], 'color_names' => ['سفید'],
                  'gallery' => ['images/products/art/1.jpg'],
                  'sizes' => ['۳۶', '۳۸', '۴۰', '۴۲'],
                  'desc' => 'کت سفید با طرح چاپی هنری روی پشت، مناسب استایل‌های روزمره و خاص.',
                  'details' => ['رنگ: سفید', 'طرح چاپی پشت کت', 'سایزها: ۳۶، ۳۸، ۴۰، ۴۲'], 'code' => 'AM-1405-005'],
        ];
    }

    function amuj_card($id, array $p)
    {
        // HikaShop listings pass 'url' and 'sizes'; static demo cards use the sample sizes
        $url    = $p['url'] ?? amuj_url('product', ['id' => $id]);
        $colors = $p['colors'] ?? [];
        $sizes  = $p['sizes'] ?? ['XS', 'S', 'M', 'L', 'XL'];
        $out    = $p['sizes_out'] ?? (isset($p['url']) ? [] : array_filter($sizes, function ($i) use ($id) { return ($id + $i) % 7 === 0; }, ARRAY_FILTER_USE_KEY));
        ?>
        <article class="az-card" data-name="<?php echo $p['name']; ?>" data-color="<?php echo $p['color_names'][0] ?? ''; ?>" data-price="<?php echo amuj_price($p['price']); ?>" data-img="<?php echo amuj_img($p['img'], 300); ?>">
            <div class="az-card-media">
                <a class="az-card-link" href="<?php echo $url; ?>">
                    <img src="<?php echo amuj_img($p['img'], 700); ?>" alt="<?php echo $p['name']; ?>" loading="lazy">
                    <img class="az-card-alt" src="<?php echo amuj_img($p['img2'], 700); ?>" alt="" loading="lazy">
                </a>
                <?php if (!empty($p['tag'])) : ?><span class="az-card-tag"><?php echo $p['tag']; ?></span><?php endif; ?>
                <button class="az-card-fav" type="button" aria-label="افزودن به علاقه‌مندی‌ها">
                    <svg width="14" height="18" viewBox="0 0 14 18"><path d="M1 1h12v16l-6-4.5L1 17z" stroke="#000" stroke-width="1.1"/></svg>
                </button>
                <button class="az-card-quick" type="button" aria-label="افزودن سریع">+</button>
                <div class="az-card-sizes">
                    <span>انتخاب سایز</span>
                    <ul>
                        <?php foreach ($sizes as $i => $size) :
                            // HikaShop: quick add puts the first colour in this size into the cart
                            $variant = isset($p['variants'], $p['size_ids'][$i])
                                ? ($p['variants'][($p['color_ids'][0] ?? 0) . '|' . $p['size_ids'][$i]][0] ?? 0) : 0; ?>
                            <li<?php echo in_array($size, $out, true) ? ' class="is-out"' : ''; ?><?php echo $variant ? ' data-variant="' . $variant . '"' : ''; ?>><?php echo $size; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <a class="az-card-name" href="<?php echo $url; ?>"><?php echo $p['name']; ?></a>
            <div class="az-card-price"><?php echo amuj_price($p['price']); ?></div>
            <?php if (count($colors) > 1) : ?>
                <div class="az-card-colors">
                    <?php foreach (array_slice($colors, 0, 3) as $hex) : ?><i style="background:<?php echo $hex; ?>"></i><?php endforeach; ?>
                    <?php if (count($colors) > 3) : ?><span>+<?php echo amuj_fa(count($colors) - 3); ?></span><?php endif; ?>
                </div>
            <?php endif; ?>
        </article>
        <?php
    }

    /** Large editorial tile that spans two grid columns */
    function amuj_feature($img, $title, $text = '')
    {
        ?>
        <a class="az-feature" href="<?php echo amuj_url('product', ['id' => 1]); ?>">
            <img src="<?php echo amuj_img($img, 1400); ?>" alt="" loading="lazy">
            <span class="az-feature-caption">
                <strong><?php echo $title; ?></strong>
                <?php if ($text) : ?><small><?php echo $text; ?></small><?php endif; ?>
            </span>
        </a>
        <?php
    }

    /** Normalise Persian/Arabic letters and digits for matching */
    function amuj_fold($text)
    {
        $text = strtr((string) $text, ['ي' => 'ی', 'ك' => 'ک', 'ة' => 'ه', 'أ' => 'ا', 'إ' => 'ا', '‌' => ' ',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9']);

        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $text)));
    }

    /**
     * HikaShop search: published products whose name, code, keywords, description, category or
     * variant colour/size contains every word of $q, plus matching shop categories.
     * Returns ['ids' => [productId...], 'categories' => [[id, name]...]] or null without HikaShop.
     */
    function amuj_hika_search($q, $limit = 48)
    {
        if (!defined('JPATH_ADMINISTRATOR')) {
            return null;
        }
        if (!function_exists('hikashop_get') && is_file(JPATH_ADMINISTRATOR . '/components/com_hikashop/helpers/helper.php')) {
            include_once JPATH_ADMINISTRATOR . '/components/com_hikashop/helpers/helper.php';
        }
        if (!function_exists('hikashop_table')) {
            return null;
        }

        // Words with Arabic ي/ك folded; each word is matched with Latin digits (codes) and Persian digits (names)
        $words = array_slice(array_filter(explode(' ', amuj_fold($q)), function ($w) { return mb_strlen($w) > 0; }), 0, 6);
        $faDigits = array_combine(range('0', '9'), ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹']);
        if (!$words) {
            return ['ids' => [], 'categories' => []];
        }

        $db   = \Joomla\CMS\Factory::getDbo();
        $p    = hikashop_table('product');
        $pc   = hikashop_table('product_category');
        $cat  = hikashop_table('category');
        $var  = hikashop_table('variant');
        $char = hikashop_table('characteristic');
        $like = function ($w) use ($db, $faDigits) {
            $forms = array_unique([$w, strtr($w, $faDigits)]);
            return array_map(function ($f) use ($db) { return $db->quote('%' . $db->escape($f, true) . '%', false); }, $forms);
        };
        // "col LIKE a OR col LIKE b" for every spelling of the word
        $any = function ($col, array $likes) { return '(' . implode(' OR ', array_map(function ($l) use ($col) { return $col . ' LIKE ' . $l; }, $likes)) . ')'; };

        $where = [];
        foreach ($words as $w) {
            $l = $like($w);
            $where[] = '(' . $any('p.product_name', $l) . ' OR ' . $any('p.product_code', $l) . ' OR ' . $any('p.product_keywords', $l)
                . ' OR ' . $any('p.product_description', $l)
                . ' OR EXISTS (SELECT 1 FROM ' . $pc . ' x JOIN ' . $cat . ' c ON c.category_id = x.category_id'
                . ' WHERE x.product_id = p.product_id AND ' . $any('c.category_name', $l) . ')'
                . ' OR EXISTS (SELECT 1 FROM ' . $p . ' v JOIN ' . $var . ' vr ON vr.variant_product_id = v.product_id'
                . ' JOIN ' . $char . ' ch ON ch.characteristic_id = vr.variant_characteristic_id'
                . ' WHERE v.product_parent_id = p.product_id AND v.product_published = 1 AND ' . $any('ch.characteristic_value', $l) . '))';
        }
        $ids = $db->setQuery('SELECT p.product_id FROM ' . $p . ' p WHERE p.product_type = "main" AND p.product_published = 1 AND '
            . implode(' AND ', $where) . ' ORDER BY p.product_created DESC', 0, (int) $limit)->loadColumn();

        $catWhere = implode(' AND ', array_map(function ($w) use ($like, $any) { return $any('category_name', $like($w)); }, $words));
        $cats = $db->setQuery('SELECT category_id AS id, category_name AS name FROM ' . $cat . ' WHERE category_type = "product"'
            . ' AND category_published = 1 AND category_parent_id > 1 AND ' . $catWhere . ' ORDER BY category_left', 0, 6)->loadAssocList();

        return ['ids' => array_map('intval', $ids), 'categories' => $cats];
    }

    /**
     * Live search: HikaShop products and categories (amuj_hika_search), or the demo products
     * outside Joomla. Returns the same shape as mod_netparsisearch: {q, total, groups: [{key, items: [...]}]}
     */
    function amuj_search($q)
    {
        $q     = mb_substr(trim((string) $q), 0, 80);

        if (mb_strlen(amuj_fold($q)) >= 2 && ($found = amuj_hika_search($q)) !== null) {
            $products = [];
            $data = amuj_hika_products(array_slice($found['ids'], 0, 12));
            foreach (array_slice($found['ids'], 0, 12) as $id) {
                if (!isset($data[$id])) {
                    continue;
                }
                $p = $data[$id];
                $products[] = ['id' => $id, 'title' => $p['name'], 'url' => $p['url'], 'image' => amuj_img($p['img'], 400),
                    'price' => amuj_price($p['price']), 'meta' => implode('، ', $p['color_names'] ?? [])];
            }
            $categories = array_map(function ($c) {
                return ['title' => $c['name'], 'url' => amuj_category_url($c['id'], amuj_url('search', ['q' => $c['name']])),
                    'image' => '', 'price' => '', 'meta' => ''];
            }, $found['categories']);

            return ['q' => $q, 'total' => count($found['ids']) + count($categories), 'groups' => array_values(array_filter([
                ['key' => 'hcategories', 'items' => $categories],
                ['key' => 'products', 'items' => $products],
            ], function ($g) { return !empty($g['items']); }))];
        }

        $terms = array_filter(explode(' ', amuj_fold($q)), function ($t) { return mb_strlen($t) > 0; });

        if (mb_strlen(amuj_fold($q)) < 2) {
            return ['q' => $q, 'total' => 0, 'groups' => []];
        }

        $matches = function ($haystack) use ($terms) {
            $haystack = amuj_fold($haystack);
            foreach ($terms as $term) {
                if (mb_strpos($haystack, $term) === false) {
                    return false;
                }
            }
            return true;
        };

        $products = [];
        foreach (amuj_products() as $id => $p) {
            if ($matches($p['name'] . ' ' . $p['cat'])) {
                $products[] = [
                    'id'    => $id,
                    'title' => $p['name'],
                    'url'   => amuj_url('product', ['id' => $id]),
                    'image' => amuj_img($p['img'], 400),
                    'price' => amuj_price($p['price']),
                    'meta'  => $p['cat'],
                ];
            }
        }

        $categories = [];
        foreach (amuj_categories() as $cat) {
            if ($matches($cat)) {
                $categories[] = ['title' => $cat, 'url' => amuj_url('category', ['cat' => $cat]), 'image' => '', 'price' => '', 'meta' => ''];
            }
        }

        $groups = array_values(array_filter([
            ['key' => 'hcategories', 'items' => $categories],
            ['key' => 'products', 'items' => array_slice($products, 0, 12)],
        ], function ($g) { return !empty($g['items']); }));

        return ['q' => $q, 'total' => count($products) + count($categories), 'groups' => $groups];
    }
}
