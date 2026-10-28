<?php
/**
 * Contact page. Article text = the lead under the heading; the form posts to the
 * "فرم تماس آموژ" plugin (ajax/amujcontact), which emails it to the address set in the plugin options.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

defined('AMUJ_BASE') || define('AMUJ_BASE', Uri::root());
defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
require_once JPATH_THEMES . '/netparsi/static/_helpers.php';

$item    = $this->item;
$contact = amuj_contact(); // global variables, group «اطلاعات تماس»
$address = $contact['address'];
$query   = rawurlencode($contact['map']);
$action  = Uri::root() . 'index.php?option=com_ajax&group=ajax&plugin=amujcontact&format=raw';
?>
<div class="az-contact">
    <header class="az-page-head">
        <span class="az-num"><?php echo $this->escape($item->title); ?></span>
        <h1>خوشحال می‌شویم از شما بشنویم</h1>
        <p class="az-muted"><?php echo strip_tags($item->text, '<a><strong><b><em><br>'); ?></p>
    </header>

    <div class="az-contact-grid">
        <div class="az-contact-cards">
            <a class="az-contact-card" href="tel:<?php echo $contact['tel']; ?>">
                <span>تلفن تماس</span>
                <strong dir="ltr"><?php echo $contact['phone']; ?></strong>
            </a>
            <a class="az-contact-card" href="<?php echo $contact['whatsapp']; ?>" target="_blank" rel="noopener">
                <span>واتس‌اپ</span>
                <strong>گفت‌وگو در واتس‌اپ</strong>
            </a>
            <a class="az-contact-card" href="<?php echo $contact['instagram']; ?>" target="_blank" rel="noopener">
                <span>اینستاگرام</span>
                <strong dir="ltr"><?php echo $contact['instagram_handle']; ?></strong>
            </a>
            <div class="az-contact-card az-contact-card--wide">
                <span>نشانی فروشگاه</span>
                <strong><?php echo $address; ?></strong>
                <div class="az-contact-maps">
                    <a href="https://neshan.org/maps/search/<?php echo $query; ?>" target="_blank" rel="noopener">مسیریابی با نشان</a>
                    <a href="https://balad.ir/search?query=<?php echo $query; ?>" target="_blank" rel="noopener">مسیریابی با بلد</a>
                    <a href="https://www.google.com/maps/search/<?php echo $query; ?>" target="_blank" rel="noopener">Google Maps</a>
                </div>
            </div>
        </div>

        <form class="az-contact-form" data-az-contact action="<?php echo $action; ?>" method="post" novalidate>
            <h2 class="az-title">ارسال پیام</h2>
            <div class="az-field"><label for="c-name">نام و نام خانوادگی</label><input class="az-input" id="c-name" name="name" maxlength="100" required></div>
            <div class="az-field"><label for="c-phone">شماره موبایل</label><input class="az-input" id="c-phone" name="phone" type="tel" inputmode="numeric" dir="ltr" maxlength="13" required></div>
            <div class="az-field"><label for="c-topic">موضوع</label>
                <select class="az-select" id="c-topic" name="topic">
                    <option>پیگیری سفارش</option>
                    <option>مشاوره سایز</option>
                    <option>تعویض و مرجوعی</option>
                    <option>هماهنگی بازدید از فروشگاه</option>
                    <option>سایر موارد</option>
                </select>
            </div>
            <div class="az-field"><label for="c-msg">پیام شما</label><textarea class="az-textarea" id="c-msg" name="message" maxlength="3000" required></textarea></div>
            <!-- left empty by people; bots fill it -->
            <div hidden aria-hidden="true"><input name="website" tabindex="-1" autocomplete="off"></div>
            <input type="hidden" name="<?php echo Session::getFormToken(); ?>" value="1">
            <p class="az-auth-error" data-error></p>
            <button class="az-btn" type="submit">ارسال پیام</button>
            <div class="az-contact-done" hidden>
                <strong>پیام شما ارسال شد</strong>
                <span class="az-muted">همکاران ما به‌زودی با شما تماس می‌گیرند.</span>
            </div>
        </form>
    </div>
</div>
