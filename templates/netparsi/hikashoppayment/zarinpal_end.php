<?php
/**
 * Zarinpal hand-off inside the Amuj "redirecting to the gateway" box (override of plugins/hikashoppayment/zarinpal/zarinpal_end.php).
 */
defined('_JEXEC') or die('Restricted access');
?>
<div class="hikashop_zarinpal_end" id="hikashop_zarinpal_end">
    <div class="az-co-redirect-spin" uk-spinner></div>
    <p class="az-small az-muted">اگر تا چند ثانیه منتقل نشدید، <a class="az-link" href="<?php echo htmlspecialchars($this->routeTo); ?>">اینجا را بزنید</a>.</p>
    <script type="text/javascript">window.location = <?php echo json_encode($this->routeTo); ?>;</script>
</div>
