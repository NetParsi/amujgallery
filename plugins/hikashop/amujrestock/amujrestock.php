<?php
/**
 * Amuj Gallery: "موجود شد خبرم کن".
 * Customers put sold-out variants in HikaShop's waitlist (template button + plugin ajax/amujcontact, form=restock).
 * When such a variant has stock again (a product saved in the admin, an order cancelled, or HikaShop's cron),
 * each waiting customer gets a message in the customer panel (customer club), an SMS (club SMS queue, pattern
 * from this plugin's options) and an email; then the request is removed.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

class plgHikashopAmujrestock extends CMSPlugin
{
    /** Requests handled per run (the rest go in the next run) */
    private const BATCH = 50;

    private $running = false;

    public function onAfterProductUpdate(&$element)
    {
        $this->process();
    }

    public function onAfterProductCreate(&$element)
    {
        $this->process();
    }

    public function onAfterOrderUpdate(&$order, &$send_email)
    {
        // A cancelled / refunded order puts its products back in stock
        if (!empty($order->order_status) && in_array($order->order_status, ['cancelled', 'refunded'], true)) {
            $this->process();
        }
    }

    public function onHikashopCronTrigger(&$messages)
    {
        $count = $this->process();
        $messages[] = 'Amuj restock notifications: ' . (int) $count;
    }

    /** Notify every waiting customer whose variant is available again; returns the number of requests handled */
    private function process()
    {
        if ($this->running) {
            return 0;
        }
        $this->running = true;
        $db = Factory::getDbo();

        try {
            $rows = $db->setQuery('SELECT w.waitlist_id, w.product_id, w.email, w.name, p.product_parent_id, p.product_type'
                . ' FROM #__hikashop_waitlist w JOIN #__hikashop_product p ON p.product_id = w.product_id'
                . ' WHERE p.product_published = 1 AND (p.product_quantity > 0 OR p.product_quantity = -1)'
                . ' ORDER BY w.date ASC', 0, self::BATCH)->loadObjectList();
        } catch (\Throwable $e) {
            $this->running = false;
            return 0;
        }

        $club = JPATH_ADMINISTRATOR . '/components/com_club/src/Helper/ClubHelper.php';
        if (is_file($club)) {
            require_once $club;
        }
        $clubHelper = '\Joomla\Component\Club\Administrator\Helper\ClubHelper';
        $hasClub = class_exists($clubHelper);
        $site = rtrim(defined('HIKASHOP_LIVE') ? HIKASHOP_LIVE : \Joomla\CMS\Uri\Uri::root(), '/') . '/';
        $done = 0;

        foreach ($rows as $row) {
            $mainId = (int) ($row->product_parent_id ?: $row->product_id);
            $name   = (string) $db->setQuery('SELECT product_name FROM #__hikashop_product WHERE product_id = ' . $mainId)->loadResult();
            $parts  = $db->setQuery('SELECT c.characteristic_value, pc.characteristic_alias FROM #__hikashop_variant v'
                . ' JOIN #__hikashop_characteristic c ON c.characteristic_id = v.variant_characteristic_id'
                . ' LEFT JOIN #__hikashop_characteristic pc ON pc.characteristic_id = c.characteristic_parent_id'
                . ' WHERE v.variant_product_id = ' . (int) $row->product_id)->loadAssocList('characteristic_alias', 'characteristic_value');
            $label = trim(strip_tags($name))
                . (!empty($parts['color']) ? '، ' . $parts['color'] : '')
                . (!empty($parts['size']) ? '، سایز ' . $parts['size'] : '');
            $url   = $site . 'index.php?option=com_hikashop&ctrl=product&task=show&cid=' . $mainId;
            $user  = $db->setQuery('SELECT id, name, username, email FROM #__users WHERE email = ' . $db->quote($row->email) . ' AND block = 0', 0, 1)->loadObject();

            try {
                // Panel message (customer club)
                if ($user && $hasClub) {
                    $clubHelper::addMessage((int) $user->id, 'موجود شد: ' . trim(strip_tags($name)),
                        '«' . $label . '» که منتظرش بودید دوباره موجود شد. تا تمام نشده، از صفحه محصول سفارش دهید.', 1, time());
                }
                // SMS (club SMS queue, sent by the club's SMS cron)
                $pattern = trim((string) $this->params->get('sms_pattern', ''));
                if ($user && $hasClub && $pattern !== '' && preg_match('/^09\d{9}$/', (string) $user->username)) {
                    $first = preg_split('/\s+/u', trim((string) $user->name))[0] ?: 'مشتری';
                    $clubHelper::addSmsQueue(1, [$user->username], ['code' => $pattern, 'parameters' => [$first, $label]]);
                }
                // Email (real addresses only: com_auth stores "<mobile>@<host>" when there is none)
                if ($this->params->get('send_email', 1) && $row->email && !preg_match('/^09\d{9}@/', $row->email)
                    && function_exists('hikashop_get')) {
                    $this->sendMail($row, $label, $url, $mainId);
                }
            } catch (\Throwable $e) {
                // keep going: one failing channel must not block the others or the next customers
            }

            $db->setQuery('DELETE FROM #__hikashop_waitlist WHERE waitlist_id = ' . (int) $row->waitlist_id)->execute();
            $done++;
        }

        $this->running = false;

        return $done;
    }

    /** HikaShop's "waitlist_notification" email (Amuj frame + media/com_hikashop/mail/waitlist_notification.html.modified.php) */
    private function sendMail($row, $label, $url, $mainId)
    {
        $mailClass = hikashop_get('class.mail');
        $data = (object) [
            'amuj_label' => $label,
            'amuj_url'   => $url,
            'amuj_main'  => $mainId,
            'amuj_name'  => $row->name,
            'email'      => $row->email,
            'name'       => $row->name,
            'product_id' => (int) $row->product_id,
        ];
        $mail = $mailClass->get('waitlist_notification', $data);
        if (empty($mail)) {
            return;
        }
        $mail->subject  = 'موجود شد: ' . $label;
        $mail->dst_email = $row->email;
        $mail->dst_name  = $row->name;
        $mailClass->sendMail($mail);
    }
}
