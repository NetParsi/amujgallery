<?php
/**
 * Shared by the mod_hikacategories layouts (hero, editorial, banner): theme helpers, and
 * hand-picked categories shown in the order they were selected in the module.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

if ($params->get('source_mode', 'manual') === 'manual') {
    $picked = array_flip(array_map('intval', (array) $params->get('categories', [])));
    usort($categories, static fn ($a, $b) => ($picked[(int) $a->id] ?? 999) <=> ($picked[(int) $b->id] ?? 999));
}

$e = static fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
