<?php
defined('_JEXEC') or die;

// Full results page (Enter in the live search lands here): HikaShop products matching ?q=,
// newest products when empty. Outside Joomla the demo products are searched instead.
$q     = isset($_GET['q']) ? mb_substr(trim((string) $_GET['q']), 0, 80) : '';
$found = amuj_hika_search($q !== '' ? $q : ' ');

if ($found !== null) {
    $ids = $found['ids'];
    if ($q === '') {
        $db  = \Joomla\CMS\Factory::getDbo();
        $ids = array_map('intval', $db->setQuery('SELECT product_id FROM ' . hikashop_table('product')
            . ' WHERE product_type = "main" AND product_published = 1 ORDER BY product_created DESC', 0, 12)->loadColumn());
    }
    $data     = amuj_hika_products($ids);
    $products = [];
    foreach ($ids as $id) {
        if (isset($data[$id])) {
            $products[$id] = $data[$id];
        }
    }
    $tags = \Joomla\CMS\Factory::getDbo()->setQuery('SELECT category_name FROM ' . hikashop_table('category')
        . ' WHERE category_parent_id = 2 AND category_published = 1 AND category_type = "product" ORDER BY category_left', 0, 12)->loadColumn();
} else {
    $products = amuj_products();
    if ($q !== '') {
        $result = amuj_search($q);
        $keep   = [];
        foreach ($result['groups'] as $group) {
            if ($group['key'] === 'products') {
                $keep = array_column($group['items'], 'id');
            }
        }
        $products = array_intersect_key($products, array_flip($keep));
    }
    $tags = amuj_categories();
}
?>
<div class="az-search-box">
    <form action="<?php echo AMUJ_BASE; ?>index.php" method="get">
        <input type="hidden" name="page" value="search">
        <input class="az-input" type="search" name="q" value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>" placeholder="جستجوی محصول، دسته یا رنگ...">
    </form>
    <div class="az-search-tags">
        <?php foreach ($tags as $tag) : ?>
            <a href="<?php echo amuj_url('search', ['q' => $tag]); ?>"><?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?></a>
        <?php endforeach; ?>
    </div>
</div>
<div class="az-cat-body">
    <div class="az-cat-title">
        <?php if ($q === '') : ?>پیشنهاد برای شما<?php else : ?>«<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>» — <?php echo amuj_fa(count($products)); ?> محصول<?php endif; ?>
    </div>
    <?php if ($products) : ?>
        <div class="az-grid" id="az-grid">
            <?php foreach ($products as $id => $p) : ?>
                <?php amuj_card($id, $p); ?>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <p class="az-muted" style="padding:0 var(--az-gutter)">نتیجه‌ای پیدا نشد. عبارت دیگری را امتحان کنید.</p>
    <?php endif; ?>
</div>
