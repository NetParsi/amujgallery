<?php
defined('_JEXEC') or die;

$user = amuj_current_user();
$rows = amuj_transactions();

amuj_account_open('credits');
?>
<h1 class="az-account-title">کیف پول</h1>

<div class="az-dash-stat az-dash-stat--big az-dash-stat--accent az-wallet-hero">
    <span>موجودی کیف پول</span>
    <strong><?php echo amuj_price($user['wallet']); ?></strong>
    <small class="az-muted">تمام موجودی در پرداخت سفارش‌ها قابل استفاده است.</small>
</div>

<section class="az-dash-section">
    <div class="az-dash-head"><h2>تاریخچه تراکنش‌ها</h2></div>
    <div class="az-tx-list">
        <?php foreach ($rows as $r) : $in = $r['amount'] > 0; ?>
            <div class="az-tx">
                <span class="az-tx-icon az-tx-icon--<?php echo $in ? 'in' : 'out'; ?>" aria-hidden="true"><?php echo $in ? '+' : '−'; ?></span>
                <div class="az-tx-info">
                    <strong><?php echo $r['title']; ?></strong>
                    <span class="az-muted"><?php echo $r['date']; ?></span>
                </div>
                <strong class="az-tx-amount <?php echo $in ? 'is-in' : 'is-out'; ?>"><?php echo ($in ? '+ ' : '− ') . amuj_price(abs($r['amount'])); ?></strong>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php amuj_account_close(); ?>
