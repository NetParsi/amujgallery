<?php
/**
 * Shared by the mod_hikaproducts layouts: Amuj card data for the module's products (same cards as
 * the category page) and the "view all" link (module option "Menu item", else the category's shop page).
 */
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

$ids   = array_map(static fn ($item) => (int) $item->id, $items);
$data  = amuj_hika_products($ids);
$cards = [];
foreach ($ids as $id) {
    if (isset($data[$id])) {
        $cards[$id] = $data[$id];
    }
}

$cats = array_values(array_filter(array_map('intval', (array) $params->get('category_ids', []))));
$more = $params->get('itemid')
    ? Route::_('index.php?Itemid=' . (int) $params->get('itemid'))
    : (count($cats) === 1 ? amuj_category_url($cats[0]) : '');
$heading = htmlspecialchars($module->title, ENT_QUOTES, 'UTF-8');
