<?php
defined('_JEXEC') or die;

$products = amuj_products();
$orders   = amuj_orders();
$filters  = ['all' => 'همه', 'active' => 'جاری', 'done' => 'تحویل شده', 'cancelled' => 'لغو شده'];

amuj_account_open('orders');
?>
<h1 class="az-account-title">سفارش‌های من</h1>

<ul class="az-tabs az-order-filter" data-az-filter>
    <?php foreach ($filters as $key => $label) : ?>
        <li><a href="#" data-filter="<?php echo $key; ?>"<?php echo $key === 'all' ? ' class="is-active"' : ''; ?>><?php echo $label; ?></a></li>
    <?php endforeach; ?>
</ul>

<div class="az-order-list">
    <?php foreach ($orders as $no => $order) :
        [$label, $mod] = amuj_order_status($order);
        $group = $mod === 'cancelled' ? 'cancelled' : ($mod === 'done' ? 'done' : 'active'); ?>
        <a class="az-order-row" href="<?php echo amuj_url('order', ['no' => $no]); ?>" data-group="<?php echo $group; ?>">
            <div class="az-order-row-info">
                <strong dir="ltr"><?php echo amuj_fa($no); ?></strong>
                <span class="az-muted"><?php echo $order['date']; ?> · <?php echo amuj_fa(count($order['items'])); ?> کالا</span>
            </div>
            <div class="az-order-thumbs">
                <?php foreach (array_keys($order['items']) as $id) : ?><img src="<?php echo amuj_img($products[$id]['img'], 200); ?>" alt=""><?php endforeach; ?>
            </div>
            <div class="az-order-row-end">
                <span class="az-badge az-badge--<?php echo $mod; ?>"><?php echo $label; ?></span>
                <strong><?php echo amuj_price(amuj_order_total($order)); ?></strong>
                <span class="az-order-more">جزئیات سفارش</span>
            </div>
        </a>
    <?php endforeach; ?>
    <p class="az-order-empty az-muted" hidden>سفارشی در این دسته وجود ندارد.</p>
</div>
<?php amuj_account_close(); ?>
