<?php
defined('_JEXEC') or die;

$user     = amuj_current_user();
$products = amuj_products();
$orders   = amuj_orders();
$lastNo   = array_key_first($orders);
$last     = $orders[$lastNo];
[$statusLabel, $statusMod] = amuj_order_status($last);
$notes    = array_slice(amuj_notifications(), 0, 2);

amuj_account_open('account');
?>
<h1 class="az-account-title">سلام <?php echo $user['first']; ?>، خوش آمدید</h1>

<div class="az-dash-grid">
    <div class="az-club az-dash-club">
        <div class="az-club-head">
            <span class="az-club-label">باشگاه مشتریان آموژ</span>
            <span class="az-club-level"><?php echo $user['level']; ?></span>
        </div>
        <div class="az-club-bar"><span style="--p: <?php echo $user['progress']; ?>%"></span></div>
        <div class="az-club-meta">
            <span><?php echo amuj_price($user['remaining']); ?> خرید تا سطح <strong><?php echo $user['next_level']; ?></strong></span>
        </div>
    </div>
    <a class="az-dash-stat" href="<?php echo amuj_url('credits'); ?>">
        <span>موجودی کیف پول</span>
        <strong><?php echo amuj_price($user['wallet']); ?></strong>
    </a>
    <a class="az-dash-stat" href="<?php echo amuj_url('credits'); ?>">
        <span>اعتبار قابل استفاده</span>
        <strong><?php echo amuj_price($user['credit']); ?></strong>
    </a>
</div>

<section class="az-dash-section">
    <div class="az-dash-head">
        <h2>آخرین سفارش</h2>
        <a href="<?php echo amuj_url('orders'); ?>">همه سفارش‌ها</a>
    </div>
    <a class="az-order-card" href="<?php echo amuj_url('order', ['no' => $lastNo]); ?>">
        <div class="az-order-card-top">
            <div>
                <strong dir="ltr"><?php echo amuj_fa($lastNo); ?></strong>
                <span class="az-muted"><?php echo $last['date']; ?> · <?php echo amuj_price(amuj_order_total($last)); ?></span>
            </div>
            <span class="az-badge az-badge--<?php echo $statusMod; ?>"><?php echo $statusLabel; ?></span>
        </div>
        <?php $steps = amuj_order_steps(); ?>
        <ol class="az-track az-track--mini">
            <?php foreach ($steps as $i => $step) : ?>
                <li class="<?php echo $i < $last['step'] ? 'is-done' : ($i === $last['step'] ? 'is-current' : ''); ?>"><span><?php echo $step; ?></span></li>
            <?php endforeach; ?>
        </ol>
        <div class="az-order-thumbs">
            <?php foreach (array_keys($last['items']) as $id) : ?><img src="<?php echo amuj_img($products[$id]['img'], 200); ?>" alt=""><?php endforeach; ?>
        </div>
    </a>
</section>

<section class="az-dash-section">
    <div class="az-dash-head">
        <h2>پیام‌های جدید</h2>
        <a href="<?php echo amuj_url('messages'); ?>">همه پیام‌ها</a>
    </div>
    <ul class="az-notify-list az-dash-notes">
        <?php foreach ($notes as $note) : ?>
            <li class="az-note az-note--<?php echo $note['type']; ?><?php echo $note['unread'] ? ' is-unread' : ''; ?>">
                <div class="az-note-meta"><span class="az-note-type"><?php echo $note['label']; ?></span><time><?php echo $note['time']; ?></time></div>
                <strong class="az-note-title"><?php echo $note['title']; ?></strong>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php amuj_account_close(); ?>
