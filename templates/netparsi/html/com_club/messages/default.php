<?php
/**
 * Customer panel: messages (menu item "messages" -> com_club messages) in the Amuj design.
 * The club's messages (credit added / used, level change, the shop's own messages). New ones are
 * highlighted on this visit and then marked as read.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

amuj_panel_guard();
Factory::getDocument()->setTitle('پیام‌های من');

$user   = amuj_current_user();
$notes  = amuj_real_notifications($user['id'], 100);
$unread = count(array_filter($notes, function ($n) { return $n['unread']; }));

amuj_account_open('messages');

// Opening the page reads them (the counter in the side menu and the header is already printed with the old number)
if ($unread) {
    $db = Factory::getDbo();
    $db->setQuery('UPDATE #__club_messages SET seen = 1, seen_on = ' . $db->quote(date('Y-m-d H:i:s'))
        . ' WHERE seen = 0 AND user_id = ' . (int) $user['id'])->execute();
}
?>
<div class="az-order-head">
    <h1 class="az-account-title">پیام‌های من</h1>
</div>

<?php if ($notes) : ?>
    <ul class="az-tabs" data-az-filter>
        <li><a href="#" data-filter="all" class="is-active">همه (<?php echo amuj_fa(count($notes)); ?>)</a></li>
        <li><a href="#" data-filter="unread">جدید (<?php echo amuj_fa($unread); ?>)</a></li>
    </ul>

    <ul class="az-notify-list az-messages">
        <?php foreach ($notes as $note) : ?>
            <li class="az-note az-note--<?php echo $note['type']; ?><?php echo $note['unread'] ? ' is-unread' : ''; ?>" data-group="<?php echo $note['unread'] ? 'unread' : 'read'; ?>">
                <div class="az-note-meta"><span class="az-note-type"><?php echo $note['label']; ?></span><time><?php echo $note['time']; ?></time></div>
                <strong class="az-note-title"><?php echo $this->escape($note['title']); ?></strong>
                <?php if ($note['text'] && $note['text'] !== $note['title']) : ?><p class="az-note-text"><?php echo $this->escape($note['text']); ?></p><?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <p class="az-order-empty az-muted" hidden>پیام جدیدی ندارید.</p>
<?php else : ?>
    <?php amuj_empty_state('message', 'پیامی ندارید', 'پیام‌های باشگاه مشتریان مثل افزایش اعتبار، تغییر سطح و پیام‌های فروشگاه اینجا نمایش داده می‌شود.'); ?>
<?php endif; ?>
<?php amuj_account_close(); ?>
