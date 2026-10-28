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
            'help'     => ['سؤالات متداول', false],
            'about'    => ['درباره ما', false],
            'contact'  => ['تماس با ما', false],
            'policy'   => ['قوانین و راهنما', false],
        ];
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
        return ['account', 'orders', 'order', 'addresses', 'credits', 'messages'];
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

    /** Customer panel menu: page key => label */
    function amuj_account_menu()
    {
        return [
            'account'   => 'داشبورد',
            'orders'    => 'سفارش‌های من',
            'addresses' => 'آدرس‌ها',
            'credits'   => 'کیف پول',
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
        return 20000000;
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

    function amuj_url($page = 'home', array $query = [])
    {
        return AMUJ_BASE . 'index.php?' . http_build_query(['page' => $page] + $query);
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
        $url    = amuj_url('product', ['id' => $id]);
        $colors = $p['colors'] ?? [];
        ?>
        <article class="az-card">
            <div class="az-card-media">
                <a class="az-card-link" href="<?php echo $url; ?>">
                    <img src="<?php echo amuj_img($p['img'], 700); ?>" alt="<?php echo $p['name']; ?>" loading="lazy">
                    <img class="az-card-alt" src="<?php echo amuj_img($p['img2'], 700); ?>" alt="" loading="lazy">
                </a>
                <?php if ($p['tag']) : ?><span class="az-card-tag"><?php echo $p['tag']; ?></span><?php endif; ?>
                <button class="az-card-fav" type="button" aria-label="افزودن به علاقه‌مندی‌ها">
                    <svg width="14" height="18" viewBox="0 0 14 18"><path d="M1 1h12v16l-6-4.5L1 17z" stroke="#000" stroke-width="1.1"/></svg>
                </button>
                <button class="az-card-quick" type="button" aria-label="افزودن سریع">+</button>
                <div class="az-card-sizes">
                    <span>انتخاب سایز</span>
                    <ul>
                        <?php foreach (['XS', 'S', 'M', 'L', 'XL'] as $i => $size) : ?>
                            <li<?php echo ($id + $i) % 7 === 0 ? ' class="is-out"' : ''; ?>><?php echo $size; ?></li>
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
     * Demo live search over amuj_products(). Returns the same shape as
     * mod_netparsisearch: {q, total, groups: [{key, items: [...]}]}
     */
    function amuj_search($q)
    {
        $q     = mb_substr(trim((string) $q), 0, 80);
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
