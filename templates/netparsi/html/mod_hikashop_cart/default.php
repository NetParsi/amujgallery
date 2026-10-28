<?php
/**
 * HikaShop cart module (position header-cart) as the Amuj bag icon with the item count.
 * HikaShop's own cart markup ($html) is not used; the count is refreshed by netparsi.js after each add.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

$count = amuj_fa(amuj_cart_count());
?>
<a class="az-cart-link" href="<?php echo amuj_url('cart'); ?>" aria-label="سبد خرید، <?php echo $count; ?> کالا"><span class="az-txt">سبد</span><span class="az-bag"><?php echo $count; ?></span></a>
