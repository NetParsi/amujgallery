<?php
defined('_JEXEC') or die;

$notes  = amuj_notifications();
$unread = count(array_filter($notes, function ($n) { return $n['unread']; }));

amuj_account_open('messages');
?>
<div class="az-order-head">
    <h1 class="az-account-title">پیام‌های من</h1>
    <?php if ($unread) : ?><button class="az-notify-readall" type="button" data-az-readall>علامت‌گذاری همه به‌عنوان خوانده‌شده</button><?php endif; ?>
</div>

<ul class="az-tabs" data-az-filter>
    <li><a href="#" data-filter="all" class="is-active">همه (<?php echo amuj_fa(count($notes)); ?>)</a></li>
    <li><a href="#" data-filter="unread">خوانده‌نشده (<?php echo amuj_fa($unread); ?>)</a></li>
</ul>

<ul class="az-notify-list az-messages">
    <?php foreach ($notes as $note) : ?>
        <li class="az-note az-note--<?php echo $note['type']; ?><?php echo $note['unread'] ? ' is-unread' : ''; ?>" data-group="<?php echo $note['unread'] ? 'unread' : 'read'; ?>">
            <div class="az-note-meta"><span class="az-note-type"><?php echo $note['label']; ?></span><time><?php echo $note['time']; ?></time></div>
            <strong class="az-note-title"><?php echo $note['title']; ?></strong>
            <p class="az-note-text"><?php echo $note['text']; ?></p>
        </li>
    <?php endforeach; ?>
</ul>
<p class="az-order-empty az-muted" hidden>پیام خوانده‌نشده‌ای ندارید.</p>
<?php amuj_account_close(); ?>
