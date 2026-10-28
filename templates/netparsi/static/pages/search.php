<?php
defined('_JEXEC') or die;

// Full results page (Enter in the live search lands here)
$products = amuj_products();
$q        = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$ids      = array_keys($products);

if ($q !== '') {
    $result = amuj_search($q);
    $ids    = [];
    foreach ($result['groups'] as $group) {
        if ($group['key'] === 'products') {
            $ids = array_column($group['items'], 'id');
        }
    }
}
?>
<div class="az-search-box">
    <form action="<?php echo AMUJ_BASE; ?>index.php" method="get">
        <input type="hidden" name="page" value="search">
        <input class="az-input" type="search" name="q" value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>" placeholder="جستجوی محصول، دسته یا رنگ...">
    </form>
    <div class="az-search-tags">
        <?php foreach (amuj_categories() as $tag) : ?>
            <a href="<?php echo amuj_url('search', ['q' => $tag]); ?>"><?php echo $tag; ?></a>
        <?php endforeach; ?>
    </div>
</div>
<div class="az-cat-body">
    <div class="az-cat-title">
        <?php if ($q === '') : ?>پیشنهاد برای شما<?php else : ?>«<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>» — <?php echo amuj_fa(count($ids)); ?> محصول<?php endif; ?>
    </div>
    <?php if ($ids) : ?>
        <div class="az-grid" id="az-grid">
            <?php foreach ($ids as $id) : ?>
                <?php amuj_card($id, $products[$id]); ?>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <p class="az-muted" style="padding:0 var(--az-gutter)">نتیجه‌ای پیدا نشد. عبارت دیگری را امتحان کنید.</p>
    <?php endif; ?>
</div>
