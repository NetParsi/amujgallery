<?php
/**
 * Customer panel: wishlist (menu item "wishlist" -> HikaShop cart view of type wishlist) in the Amuj design.
 * Product cards of the customer's HikaShop wishlist; the heart / "حذف" removes a product (amujcontact plugin).
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

amuj_panel_guard();
Factory::getDocument()->setTitle('علاقه‌مندی‌ها');

$ids   = amuj_wishlist_ids();
$items = $ids ? amuj_hika_products($ids) : [];
// Keep the wishlist order (newest first)
$items = array_filter(array_replace(array_flip($ids), $items), 'is_array');

// Sold-out variants the customer is waiting for ("موجود شد خبرم کن")
$waiting = [];
$waitIds = amuj_restock_requests();
if ($waitIds) {
    $db = Factory::getDbo();
    $vrows = $db->setQuery('SELECT product_id, IF(product_parent_id > 0, product_parent_id, product_id) AS main FROM #__hikashop_product'
        . ' WHERE product_id IN (' . implode(',', $waitIds) . ')')->loadObjectList();
    $wmains = amuj_hika_products(array_values(array_unique(array_map(function ($r) { return (int) $r->main; }, $vrows))));
    foreach ($vrows as $r) {
        $p = $wmains[(int) $r->main] ?? null;
        if (!$p) {
            continue;
        }
        $label = '';
        $img = $p['img'];
        foreach ($p['variants'] as $key => $v) {
            if ((int) $v[0] === (int) $r->product_id) {
                [$cid, $sid] = explode('|', $key);
                $ci = array_search((int) $cid, $p['color_ids']);
                $si = array_search((int) $sid, $p['size_ids']);
                $label = implode('، ', array_filter([$ci !== false ? $p['color_names'][$ci] : '', $si !== false ? 'سایز ' . $p['sizes'][$si] : '']));
                $img = $p['color_images'][(int) $cid] ?? $img;
                break;
            }
        }
        $waiting[] = ['id' => (int) $r->product_id, 'name' => $p['name'], 'label' => $label, 'img' => $img, 'url' => $p['url']];
    }
}

amuj_account_open('wishlist');
?>
<h1 class="az-account-title">علاقه‌مندی‌ها <span class="az-muted az-small" data-az-wish-count<?php echo $items ? '' : ' hidden'; ?>>(<?php echo amuj_fa(count($items)); ?> کالا)</span></h1>

<div class="az-grid az-wish-grid" data-az-wish-page<?php echo $items ? '' : ' hidden'; ?>>
    <?php foreach ($items as $id => $p) : ?>
        <div class="az-wish-item" data-id="<?php echo (int) $id; ?>">
            <?php amuj_card($id, $p); ?>
            <button class="az-wish-remove" type="button" data-az-wish-remove aria-label="حذف از علاقه‌مندی‌ها">حذف</button>
        </div>
    <?php endforeach; ?>
</div>
<div data-az-wish-empty<?php echo $items ? ' hidden' : ''; ?>>
    <?php amuj_empty_state('heart', 'لیست علاقه‌مندی‌های شما خالی است', 'با زدن نشان روی هر محصول، آن را اینجا نگه دارید تا بعداً راحت پیدایش کنید.', 'مشاهده محصولات', amuj_url('category')); ?>
</div>
<?php if ($waiting) : ?>
    <section class="az-dash-section" data-az-restock-list>
        <div class="az-dash-head"><h2>منتظر موجود شدن</h2></div>
        <p class="az-muted az-small">وقتی هر کدام موجود شد، پیامک و پیام در حساب کاربری‌تان دریافت می‌کنید.</p>
        <div class="az-restock-list">
            <?php foreach ($waiting as $w) : ?>
                <div class="az-restock-item" data-variant="<?php echo $w['id']; ?>">
                    <a href="<?php echo $w['url']; ?>"><img src="<?php echo amuj_img($w['img'], 200); ?>" alt=""></a>
                    <div>
                        <a href="<?php echo $w['url']; ?>"><?php echo $this->escape($w['name']); ?></a>
                        <small><?php echo $this->escape(amuj_fa($w['label'])); ?></small>
                    </div>
                    <button class="az-remove" type="button" data-az-restock-cancel>لغو</button>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
<?php amuj_account_close(); ?>
