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

    /** Page key => [title, header overlays the content (transparent header)] */
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
            'login'    => ['ورود', false],
            'register' => ['ایجاد حساب کاربری', false],
            'account'  => ['حساب کاربری من', false],
            'help'     => ['راهنما و پشتیبانی', false],
            'about'    => ['درباره ما', false],
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
                $on = !empty($_COOKIE['amuj_demo_user']);
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
            'credit'     => 300000,
            'unread'     => 2,
        ];
    }

    /**
     * Customer notifications (sample). Automatic ones come from the customer-club
     * extension (credit added, level up); manual ones are sent by the shop.
     */
    function amuj_notifications()
    {
        return [
            ['type' => 'credit', 'label' => 'اعتبار', 'title' => '۳۰۰٬۰۰۰ تومان اعتبار به حساب شما اضافه شد',
             'text' => 'این اعتبار تا ۳۰ آذر ۱۴۰۵ قابل استفاده است.', 'time' => '۲ ساعت پیش', 'unread' => true],
            ['type' => 'level', 'label' => 'باشگاه مشتریان', 'title' => 'تبریک! سطح شما به «نقره‌ای» ارتقا یافت',
             'text' => 'از این پس از ارسال رایگان و پیش‌خرید کالکشن‌های جدید بهره‌مند می‌شوید.', 'time' => 'دیروز', 'unread' => true],
            ['type' => 'manual', 'label' => 'گالری آموژ', 'title' => 'کالکشن پاییز ۱۴۰۵ برای اعضای باشگاه زودتر در دسترس است',
             'text' => 'تا پایان هفته، پیش از عرضه عمومی خرید کنید.', 'time' => '۳ روز پیش', 'unread' => false],
            ['type' => 'credit', 'label' => 'اعتبار', 'title' => '۱۵۰٬۰۰۰ تومان اعتبار هدیه تولد',
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
     * Demo image. Drop your own photos in /images/demo/{n}.jpg to replace the
     * placeholder photos without touching the markup.
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

        $photos = [
            '1515886657613-9f3515b0c78f', '1496747611176-843222e1e57c', '1509631179647-0177331693ae',
            '1539109136881-3be0616acf4b', '1485968579580-b6d095142e6e', '1469334031218-e382a71b716b',
            '1529139574466-a303027c1d8b', '1594633312681-425c7b97ccd1', '1554412933-514a83d2f3c8',
            '1525507119028-ed4c629a60a3', '1591047139829-d91aecb6caea', '1490481651871-ab68de25d43d',
            '1483985988355-763728e1935b', '1445205170230-053b83016050', '1434389677669-e08b4cac3105',
            '1487222477894-8943e31ef7b2', '1475180098004-ca77a66827be', '1520975954732-35dd22299614',
            '1581044777550-4cfa60707c03', '1544441893-675973e31985', '1552374196-1ab2a1c593e8',
            '1622519407650-3df9883f76a5',
        ];

        $id = $photos[($n - 1) % count($photos)];

        return 'https://images.unsplash.com/photo-' . $id . '?auto=format&fit=crop&w=' . $width . '&q=75';
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
            1  => ['name' => 'مانتو کتان بلند',          'cat' => 'مانتو',   'price' => 2290000, 'img' => 4,  'img2' => 1,  'tag' => 'جدید',   'colors' => [$beige, $black, $cream]],
            2  => ['name' => 'کت بلیزر کراپ',            'cat' => 'کت',      'price' => 2790000, 'img' => 1,  'img2' => 5,  'tag' => '',       'colors' => [$black, $cream]],
            3  => ['name' => 'پالتو پشمی کمربنددار',     'cat' => 'پالتو',   'price' => 5490000, 'img' => 8,  'img2' => 9,  'tag' => 'جدید',   'colors' => [$brown, $black, $gray, $beige]],
            4  => ['name' => 'بارانی کلاسیک دوبل',       'cat' => 'بارانی',  'price' => 4190000, 'img' => 3,  'img2' => 7,  'tag' => '',       'colors' => [$beige, $navy]],
            5  => ['name' => 'پیراهن ساتن میدی',         'cat' => 'پیراهن',  'price' => 1690000, 'img' => 2,  'img2' => 3,  'tag' => '',       'colors' => [$black, $brown, $navy]],
            6  => ['name' => 'ست کت و شلوار کرپ',        'cat' => 'ست',      'price' => 3890000, 'img' => 5,  'img2' => 4,  'tag' => 'پرفروش', 'colors' => [$black, $cream]],
            7  => ['name' => 'شلوار راسته فاق بلند',     'cat' => 'شلوار',   'price' => 1390000, 'img' => 7,  'img2' => 6,  'tag' => '',       'colors' => [$black, $beige, $gray, $navy, $cream]],
            8  => ['name' => 'دامن پلیسه میدی',          'cat' => 'دامن',    'price' => 1590000, 'img' => 6,  'img2' => 2,  'tag' => '',       'colors' => [$black, $beige]],
            9  => ['name' => 'شومیز حریر یقه‌دار',       'cat' => 'شومیز',   'price' => 1190000, 'img' => 9,  'img2' => 8,  'tag' => '',       'colors' => [$cream, $black, $navy]],
            10 => ['name' => 'شال نخی بلند',             'cat' => 'اکسسوری', 'price' => 490000,  'img' => 10, 'img2' => 1,  'tag' => '',       'colors' => [$beige, $black, $brown, $cream, $gray, $navy]],
            11 => ['name' => 'مانتو جلوباز لینن',        'cat' => 'مانتو',   'price' => 1990000, 'img' => 11, 'img2' => 12, 'tag' => 'جدید',   'colors' => [$cream, $beige]],
            12 => ['name' => 'پالتو بلند اورسایز',       'cat' => 'پالتو',   'price' => 5990000, 'img' => 13, 'img2' => 14, 'tag' => '',       'colors' => [$gray, $black]],
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
