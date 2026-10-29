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
<?php amuj_account_close(); ?>
