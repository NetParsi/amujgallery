<?php
/**
 * Customer panel: "سفارش‌های من" (menu item "orders" -> HikaShop order listing) in the Amuj design.
 * Orders of the signed-in customer, newest first, with status filter tabs.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

amuj_panel_guard();
Factory::getDocument()->setTitle('سفارش‌های من');

$user    = amuj_current_user();
$orders  = amuj_customer_orders($user['id']);
$thumbs  = amuj_order_thumbs(array_column($orders, 'order_id'));
$filters = ['all' => 'همه', 'active' => 'جاری', 'done' => 'تحویل شده', 'cancelled' => 'لغو شده'];

amuj_account_open('orders');
?>
<h1 class="az-account-title">سفارش‌های من</h1>

<?php if (!$orders) : ?>
    <p class="az-muted">هنوز سفارشی ثبت نکرده‌اید. <a class="az-link" href="<?php echo amuj_url('category'); ?>">مشاهده جدیدترین‌ها</a></p>
<?php else : ?>
    <ul class="az-tabs az-order-filter" data-az-filter>
        <?php foreach ($filters as $key => $label) : ?>
            <li><a href="#" data-filter="<?php echo $key; ?>"<?php echo $key === 'all' ? ' class="is-active"' : ''; ?>><?php echo $label; ?></a></li>
        <?php endforeach; ?>
    </ul>

    <div class="az-order-list">
        <?php foreach ($orders as $order) :
            [$label, $mod] = amuj_order_state($order->order_status);
            $group = $mod === 'cancelled' ? 'cancelled' : ($mod === 'done' ? 'done' : 'active');
            $imgs  = $thumbs[$order->order_id] ?? [];
            ?>
            <a class="az-order-row" href="<?php echo amuj_order_url($order->order_id); ?>" data-group="<?php echo $group; ?>">
                <div class="az-order-row-info">
                    <strong dir="ltr"><?php echo amuj_fa($order->order_number); ?></strong>
                    <span class="az-muted"><?php echo amuj_jdate($order->order_created); ?> · <?php echo amuj_fa(count($imgs)); ?> کالا</span>
                </div>
                <div class="az-order-thumbs">
                    <?php foreach (array_slice($imgs, 0, 4) as $img) : ?><img src="<?php echo $img; ?>" alt=""><?php endforeach; ?>
                </div>
                <div class="az-order-row-end">
                    <span class="az-badge az-badge--<?php echo $mod; ?>"><?php echo $label; ?></span>
                    <strong><?php echo amuj_price($order->order_full_price); ?></strong>
                    <span class="az-order-more">جزئیات سفارش</span>
                </div>
            </a>
        <?php endforeach; ?>
        <p class="az-order-empty az-muted" hidden>سفارشی در این دسته وجود ندارد.</p>
    </div>
<?php endif; ?>
<?php amuj_account_close(); ?>
