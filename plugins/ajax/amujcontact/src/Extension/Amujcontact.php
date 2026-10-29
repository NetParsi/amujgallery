<?php

/**
 * @package     Amuj Gallery
 * @subpackage  Ajax.amujcontact
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Plugin\Ajax\Amujcontact\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

/**
 * Contact form of the Amuj template (name, mobile, topic, message) -> email to the address in the plugin options.
 * POST to index.php?option=com_ajax&group=ajax&plugin=amujcontact&format=raw with the form token;
 * answers {"ok": bool, "message": string}. With form=newsletter it stores a footer newsletter sign-up instead.
 */
final class Amujcontact extends CMSPlugin implements SubscriberInterface
{
    /** Seconds a visitor waits between two messages */
    private const WAIT = 60;

    /** Topics offered by the form; anything else is stored as "سایر موارد" */
    private const TOPICS = ['پیگیری سفارش', 'مشاوره سایز', 'تعویض و مرجوعی', 'هماهنگی بازدید از فروشگاه', 'سایر موارد'];

    public static function getSubscribedEvents(): array
    {
        return ['onAjaxAmujcontact' => 'onAjaxAmujcontact'];
    }

    public function onAjaxAmujcontact(Event $event): void
    {
        $app   = $this->getApplication();
        $input = $app->getInput()->post;

        // Public XML sitemap (GET, no form token): index.php?option=com_ajax&group=ajax&plugin=amujcontact&format=raw&form=sitemap
        if ($app->getInput()->get->getCmd('form') === 'sitemap') {
            $this->sitemap();
        }

        if (!Session::checkToken()) {
            $this->respond(false, 'زمان صفحه تمام شده است؛ صفحه را تازه کنید و دوباره بفرستید.');
        }

        if ($input->getCmd('form') === 'newsletter') {
            $this->newsletter();
        }

        if ($input->getCmd('form') === 'readall') {
            $this->readAll();
        }

        if ($input->getCmd('form') === 'wish') {
            $this->wish();
        }

        if ($input->getCmd('form') === 'restock') {
            $this->restock();
        }

        // Bots fill the hidden "website" field: answer as if sent, send nothing
        if ($input->getString('website', '') !== '') {
            $this->respond(true, 'پیام شما ارسال شد');
        }

        $name    = trim(preg_replace('/\s+/u', ' ', $input->getString('name', '')));
        $phone   = preg_replace('/\D/', '', strtr($input->getString('phone', ''), [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]));
        $topic   = $input->getString('topic', '');
        $topic   = \in_array($topic, self::TOPICS, true) ? $topic : 'سایر موارد';
        $message = trim($input->getString('message', ''));

        if (mb_strlen($name) < 3 || !preg_match('/^09\d{9}$/', $phone) || mb_strlen($message) < 4) {
            $this->respond(false, 'لطفاً نام، شماره موبایل (مثل ۰۹۱۲۳۴۵۶۷۸۹) و متن پیام را کامل وارد کنید.');
        }

        $session = $app->getSession();
        if (time() - (int) $session->get('amujcontact.last', 0) < self::WAIT) {
            $this->respond(false, 'پیام قبلی شما رسید؛ برای پیام بعدی یک دقیقه صبر کنید.');
        }

        // Plugin option, else the global variable contact_email (com_globalvars), else the site email
        $recipient = trim((string) $this->params->get('recipient', ''));
        $globals   = JPATH_ADMINISTRATOR . '/components/com_globalvars/helpers/globalvars.php';
        if ($recipient === '' && is_file($globals)) {
            require_once $globals;
            $recipient = trim((string) \GlobalVarsHelper::get('contact_email', ''));
        }
        $recipient = $recipient ?: $app->get('mailfrom');
        $body = implode("\n", [
            'پیام جدید از فرم تماس سایت ' . $app->get('sitename'),
            '',
            'نام: ' . mb_substr($name, 0, 100),
            'موبایل: ' . $phone,
            'موضوع: ' . $topic,
            'زمان: ' . Factory::getDate('now', 'Asia/Tehran')->format('Y-m-d H:i', true),
            '',
            mb_substr($message, 0, 3000),
        ]);

        try {
            $mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer();
            $mailer->setSender([$app->get('mailfrom'), $app->get('fromname')]);
            $mailer->addRecipient($recipient);
            $mailer->setSubject('فرم تماس: ' . $topic . ' — ' . mb_substr($name, 0, 60));
            $mailer->isHtml(false);
            $mailer->setBody($body);
            $sent = $mailer->Send();
        } catch (\Throwable $e) {
            $sent = false;
        }

        if ($sent !== true) {
            $this->respond(false, 'ارسال پیام انجام نشد. لطفاً کمی بعد دوباره امتحان کنید یا تماس بگیرید.');
        }

        $session->set('amujcontact.last', time());
        $this->respond(true, 'پیام شما ارسال شد');
    }

    /** Footer newsletter: one email or mobile number per row in #__amuj_newsletter (repeats are fine) */
    private function newsletter(): void
    {
        $app   = $this->getApplication();
        $input = $app->getInput()->post;

        if ($input->getString('website', '') !== '') {
            $this->respond(true, 'عضویت شما ثبت شد');
        }

        $value  = trim(strtr($input->getString('contact', ''), [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]));
        $mobile = preg_replace('/[\s-]/', '', $value);

        if (preg_match('/^09\d{9}$/', $mobile)) {
            [$contact, $type] = [$mobile, 'mobile'];
        } elseif (filter_var($value, FILTER_VALIDATE_EMAIL) && mb_strlen($value) <= 191) {
            [$contact, $type] = [mb_strtolower($value), 'email'];
        } else {
            $this->respond(false, 'یک ایمیل یا شماره موبایل معتبر وارد کنید.');
        }

        $session = $app->getSession();
        if (time() - (int) $session->get('amujcontact.news', 0) < 10) {
            $this->respond(false, 'چند لحظه بعد دوباره امتحان کنید.');
        }
        $session->set('amujcontact.news', time());

        try {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $db->setQuery('INSERT IGNORE INTO ' . $db->quoteName('#__amuj_newsletter') . ' (contact, type, created) VALUES ('
                . $db->quote($contact) . ', ' . $db->quote($type) . ', ' . $db->quote(Factory::getDate()->toSql()) . ')')->execute();
        } catch (\Throwable $e) {
            $this->respond(false, 'ثبت عضویت انجام نشد. لطفاً کمی بعد دوباره امتحان کنید.');
        }

        $this->respond(true, 'عضویت شما ثبت شد');
    }

    /** Customer panel: mark every customer-club message of the signed-in customer as read */
    private function readAll(): void
    {
        $user = $this->getApplication()->getIdentity();
        if (!$user || $user->guest) {
            $this->respond(false, 'ابتدا وارد حساب کاربری شوید.');
        }

        try {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $db->setQuery('UPDATE ' . $db->quoteName('#__club_messages') . ' SET seen = 1, seen_on = ' . $db->quote(date('Y-m-d H:i:s'))
                . ' WHERE seen = 0 AND user_id = ' . (int) $user->id)->execute();
        } catch (\Throwable $e) {
            $this->respond(false, 'انجام نشد.');
        }

        $this->respond(true, 'همه پیام‌ها خوانده شد');
    }

    /**
     * Product cards' heart: add / remove a product (main product id) in the customer's HikaShop wishlist
     * (a cart of type "wishlist", created on first use). Answers {"ok", "message", "active", "count"}.
     */
    private function wish(): void
    {
        $app   = $this->getApplication();
        $user  = $app->getIdentity();
        $input = $app->getInput()->post;
        if (!$user || $user->guest) {
            $this->respond(false, 'برای ذخیره علاقه‌مندی‌ها ابتدا وارد حساب کاربری شوید.');
        }
        $productId = $input->getInt('product_id', 0);
        $remove    = $input->getCmd('op') === 'remove';

        $helper = JPATH_ADMINISTRATOR . '/components/com_hikashop/helpers/helper.php';
        if (!$productId || !is_file($helper)) {
            $this->respond(false, 'انجام نشد.');
        }
        include_once $helper;

        try {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $exists = (int) $db->setQuery('SELECT product_id FROM #__hikashop_product WHERE product_id = ' . $productId
                . ' AND product_published = 1 AND product_type = "main"')->loadResult();
            $hkUser = (int) hikashop_loadUser(false);
            if (!$exists || !$hkUser) {
                $this->respond(false, 'انجام نشد.');
            }

            $cartId = (int) $db->setQuery('SELECT cart_id FROM #__hikashop_cart WHERE user_id = ' . $hkUser
                . ' AND cart_type = "wishlist" ORDER BY cart_current DESC, cart_id LIMIT 1')->loadResult();
            if (!$cartId && !$remove) {
                $cart = (object) ['user_id' => $hkUser, 'session_id' => '', 'cart_modified' => time(), 'cart_type' => 'wishlist',
                    'cart_coupon' => '', 'cart_currency_id' => (int) hikashop_getCurrency(), 'cart_name' => 'علاقه‌مندی‌ها', 'cart_current' => 1,
                    'cart_share' => 'nobody', 'cart_params' => '{}', 'cart_fields' => '{}', 'cart_ip' => '', 'cart_shipping_ids' => '',
                    'cart_shipping_address_ids' => '', 'cart_payment_id' => 0, 'cart_billing_address_id' => 0];
                $db->insertObject('#__hikashop_cart', $cart, 'cart_id');
                $cartId = (int) $cart->cart_id;
            }

            $has = $cartId ? (int) $db->setQuery('SELECT COUNT(*) FROM #__hikashop_cart_product WHERE cart_id = ' . $cartId
                . ' AND product_id = ' . $productId)->loadResult() : 0;
            if ($remove || $has) {
                if ($cartId) {
                    $db->setQuery('DELETE FROM #__hikashop_cart_product WHERE cart_id = ' . $cartId . ' AND product_id = ' . $productId)->execute();
                }
                $active = false;
            } else {
                $row = (object) ['cart_id' => $cartId, 'product_id' => $productId, 'cart_product_quantity' => 1,
                    'cart_product_modified' => time(), 'cart_product_parent_id' => 0, 'cart_product_option_parent_id' => 0];
                $db->insertObject('#__hikashop_cart_product', $row);
                $active = true;
            }
            if ($cartId) {
                $db->setQuery('UPDATE #__hikashop_cart SET cart_modified = ' . time() . ' WHERE cart_id = ' . $cartId)->execute();
            }
            $count = $cartId ? (int) $db->setQuery('SELECT COUNT(*) FROM #__hikashop_cart_product WHERE cart_id = ' . $cartId)->loadResult() : 0;
        } catch (\Throwable $e) {
            $this->respond(false, 'انجام نشد.');
        }

        $app->setHeader('Content-Type', 'application/json; charset=utf-8', true);
        $app->sendHeaders();
        echo json_encode(['ok' => true, 'message' => $active ? 'به علاقه‌مندی‌ها اضافه شد' : 'از علاقه‌مندی‌ها حذف شد',
            'active' => $active, 'count' => $count], JSON_UNESCAPED_UNICODE);
        $app->close();
    }

    /**
     * "موجود شد خبرم کن": add / remove a sold-out variant in HikaShop's waitlist for the signed-in customer
     * (identified by the account email; the plugin "hikashop/amujrestock" sends the notifications).
     */
    private function restock(): void
    {
        $app   = $this->getApplication();
        $user  = $app->getIdentity();
        $input = $app->getInput()->post;
        if (!$user || $user->guest || !$user->email) {
            $this->respond(false, 'ابتدا وارد حساب کاربری شوید.');
        }
        $variantId = $input->getInt('variant_id', 0);
        $remove    = $input->getCmd('op') === 'remove';

        try {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $exists = (int) $db->setQuery('SELECT product_id FROM #__hikashop_product WHERE product_id = ' . $variantId
                . ' AND product_published = 1')->loadResult();
            if (!$exists) {
                $this->respond(false, 'انجام نشد.');
            }
            $db->setQuery('DELETE FROM #__hikashop_waitlist WHERE product_id = ' . $variantId . ' AND email = ' . $db->quote($user->email))->execute();
            if (!$remove) {
                $row = (object) ['product_id' => $variantId, 'date' => time(), 'email' => $user->email, 'name' => $user->name,
                    'product_item_id' => 0, 'language' => $app->getLanguage()->getTag()];
                $db->insertObject('#__hikashop_waitlist', $row);
            }
        } catch (\Throwable $e) {
            $this->respond(false, 'انجام نشد.');
        }

        $app->setHeader('Content-Type', 'application/json; charset=utf-8', true);
        $app->sendHeaders();
        echo json_encode(['ok' => true, 'active' => !$remove,
            'message' => $remove ? 'درخواست لغو شد' : 'ثبت شد؛ وقتی موجود شد خبرتان می‌کنیم'], JSON_UNESCAPED_UNICODE);
        $app->close();
    }

    /**
     * XML sitemap for search engines: home, the shop menu ("amuj-main"), the public text pages ("amuj-pages" items that
     * are not noindex) and every published product at its one address (template helper amuj_product_url()).
     */
    private function sitemap(): void
    {
        $app  = $this->getApplication();
        $db   = Factory::getContainer()->get('DatabaseDriver');
        $menu = $app->getMenu('site');
        $abs  = function ($url) {
            return preg_match('#^https?://#', $url) ? $url : \Joomla\CMS\Uri\Uri::getInstance()->toString(['scheme', 'host', 'port']) . $url;
        };
        $urls = [[rtrim(\Joomla\CMS\Uri\Uri::root(), '/') . '/', null, '1.0']];

        foreach (['amuj-main', 'amuj-pages'] as $type) {
            foreach ($menu->getItems('menutype', $type) as $item) {
                if ($item->type !== 'component' || (int) $item->access !== 1 || stripos((string) $item->getParams()->get('robots'), 'noindex') !== false) {
                    continue;
                }
                $urls[] = [$abs(\Joomla\CMS\Router\Route::_('index.php?Itemid=' . $item->id)), null, $type === 'amuj-main' ? '0.8' : '0.4'];
            }
        }

        $templateHelpers = JPATH_THEMES . '/netparsi/static/_helpers.php';
        $hikashop = JPATH_ADMINISTRATOR . '/components/com_hikashop/helpers/helper.php';
        if (is_file($templateHelpers) && is_file($hikashop)) {
            include_once $hikashop;
            defined('AMUJ_BASE') || define('AMUJ_BASE', \Joomla\CMS\Uri\Uri::root());
            defined('AMUJ_ROOT') || define('AMUJ_ROOT', JPATH_ROOT);
            require_once $templateHelpers;
            $rows = $db->setQuery('SELECT product_id, product_modified FROM #__hikashop_product'
                . ' WHERE product_type = "main" AND product_published = 1 ORDER BY product_id')->loadObjectList();
            foreach ($rows as $r) {
                $url = amuj_product_url((int) $r->product_id);
                if ($url !== '') {
                    $urls[] = [$abs($url), (int) $r->product_modified ? gmdate('Y-m-d', (int) $r->product_modified) : null, '0.9'];
                }
            }
        }

        $app->setHeader('Content-Type', 'application/xml; charset=utf-8', true);
        $app->sendHeaders();
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as [$loc, $lastmod, $priority]) {
            echo '  <url><loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>' . ($lastmod ? '<lastmod>' . $lastmod . '</lastmod>' : '')
                . '<priority>' . $priority . '</priority></url>' . "\n";
        }
        echo '</urlset>';
        $app->close();
    }

    private function respond(bool $ok, string $message): void
    {
        $app = $this->getApplication();
        $app->setHeader('Content-Type', 'application/json; charset=utf-8', true);
        $app->sendHeaders();
        echo json_encode(['ok' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE);
        $app->close();
    }
}
