<?php
/**
 * Announcement bar (Custom module, position "announcement", layout "topbar"): every paragraph of
 * the module text is one message; they take turns (netparsi.js [data-az-rotate]).
 * {free_shipping} is replaced by the free-shipping amount in millions (global variable shop_free_shipping).
 */
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

$content = str_replace('{free_shipping}', amuj_fa(amuj_free_shipping() / 1000000), (string) $module->content);
preg_match_all('#<p[^>]*>(.*?)</p>#is', $content, $m);
$messages = array_values(array_filter(array_map('trim', $m[1] ?: [strip_tags($content, '<a><strong><b>')]), function ($t) {
    return trim(strip_tags($t)) !== '';
}));

if (!$messages) {
    return;
}
?>
<div class="az-topbar-track" data-az-rotate>
    <?php foreach ($messages as $i => $text) : ?>
        <span<?php echo $i === 0 ? ' class="is-active"' : ''; ?>><?php echo strip_tags($text, '<a><strong><b>'); ?></span>
    <?php endforeach; ?>
</div>
