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
            'category' => ['زنانه | جدیدترین‌ها', false],
            'product'  => ['جزئیات محصول', false],
            'search'   => ['جستجو', false],
            'cart'     => ['سبد خرید', false],
            'checkout' => ['تکمیل سفارش', false],
            'thankyou' => ['سفارش ثبت شد', false],
            'login'    => ['ورود', false],
            'register' => ['ایجاد حساب کاربری', false],
            'account'  => ['حساب کاربری من', false],
            'help'     => ['راهنما و پشتیبانی', false],
            'about'    => ['درباره ما', false],
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
        $local = 'images/demo/' . $n . '.jpg';

        if (is_file(AMUJ_ROOT . '/' . $local)) {
            return AMUJ_BASE . $local;
        }

        $photos = [
            '1515886657613-9f3515b0c78f', '1496747611176-843222e1e57c', '1509631179647-0177331693ae',
            '1539109136881-3be0616acf4b', '1485968579580-b6d095142e6e', '1469334031218-e382a71b716b',
            '1529139574466-a303027c1d8b', '1594633312681-425c7b97ccd1', '1554412933-514a83d2f3c8',
            '1525507119028-ed4c629a60a3', '1503342217505-b0a15ec3261c', '1516826957135-700dedea698c',
            '1507679799987-c73779587ccf', '1519085360753-af0119f7cbe7', '1488161628813-04466f872be2',
            '1503944583220-79d8926ad5e2', '1519238263530-99bdd11df2ea', '1591047139829-d91aecb6caea',
            '1490481651871-ab68de25d43d', '1483985988355-763728e1935b', '1445205170230-053b83016050',
            '1434389677669-e08b4cac3105', '1487222477894-8943e31ef7b2', '1475180098004-ca77a66827be',
            '1520975954732-35dd22299614', '1581044777550-4cfa60707c03', '1544441893-675973e31985',
            '1552374196-1ab2a1c593e8', '1622519407650-3df9883f76a5',
        ];

        $id = $photos[($n - 1) % count($photos)];

        return 'https://images.unsplash.com/photo-' . $id . '?auto=format&fit=crop&w=' . $width . '&q=75';
    }

    function amuj_products()
    {
        return [
            1  => ['name' => 'کت بلند پشمی',            'price' => 4890000, 'img' => 4,  'img2' => 1,  'tag' => 'جدید'],
            2  => ['name' => 'پیراهن ساتن یقه‌دار',      'price' => 1690000, 'img' => 2,  'img2' => 3,  'tag' => ''],
            3  => ['name' => 'شلوار راسته کرپ',          'price' => 1390000, 'img' => 3,  'img2' => 7,  'tag' => ''],
            4  => ['name' => 'بلیزر کراپ',               'price' => 2790000, 'img' => 1,  'img2' => 5,  'tag' => 'جدید'],
            5  => ['name' => 'دامن میدی پلیسه',          'price' => 1590000, 'img' => 6,  'img2' => 2,  'tag' => ''],
            6  => ['name' => 'سویشرت بافت درشت',         'price' => 1990000, 'img' => 5,  'img2' => 4,  'tag' => ''],
            7  => ['name' => 'مانتو کتان مینیمال',       'price' => 2290000, 'img' => 7,  'img2' => 6,  'tag' => 'پرفروش'],
            8  => ['name' => 'پالتو کمربنددار',          'price' => 5490000, 'img' => 8,  'img2' => 9,  'tag' => ''],
            9  => ['name' => 'تاپ بافت رکابی',           'price' => 890000,  'img' => 9,  'img2' => 8,  'tag' => ''],
            10 => ['name' => 'جین بگ فاق بلند',          'price' => 1790000, 'img' => 10, 'img2' => 1,  'tag' => ''],
            11 => ['name' => 'تی‌شرت پنبه‌ای مردانه',    'price' => 690000,  'img' => 11, 'img2' => 12, 'tag' => ''],
            12 => ['name' => 'کت تک مردانه',             'price' => 3990000, 'img' => 13, 'img2' => 14, 'tag' => 'جدید'],
        ];
    }

    function amuj_card($id, array $p)
    {
        ?>
        <article class="az-card">
            <a class="az-card-media" href="<?php echo amuj_url('product', ['id' => $id]); ?>">
                <img src="<?php echo amuj_img($p['img'], 700); ?>" alt="<?php echo $p['name']; ?>" loading="lazy">
                <img class="az-card-alt" src="<?php echo amuj_img($p['img2'], 700); ?>" alt="" loading="lazy">
                <?php if ($p['tag']) : ?><span class="az-card-tag"><?php echo $p['tag']; ?></span><?php endif; ?>
            </a>
            <div class="az-card-info">
                <a href="<?php echo amuj_url('product', ['id' => $id]); ?>" class="az-card-name"><?php echo $p['name']; ?></a>
                <button class="az-card-add" type="button" aria-label="افزودن به سبد">+</button>
            </div>
            <div class="az-card-price"><?php echo amuj_price($p['price']); ?></div>
        </article>
        <?php
    }
}
