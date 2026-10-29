<?php

/**
 * Customer data for the header and the customer panel when a real customer is signed in:
 * club level / wallet / messages (customer-club extension com_club) and orders (HikaShop).
 * Loaded by _helpers.php; the demo data in _helpers.php stays for visitors of the static pages.
 */

defined('_JEXEC') or die;

if (!function_exists('amuj_real_user')) {

    /** The signed-in customer in the shape of amuj_current_user() */
    function amuj_real_user($userId)
    {
        $db   = \Joomla\CMS\Factory::getDbo();
        $row  = $db->setQuery('SELECT id, name, username, email, birthday, club_level_id FROM #__users WHERE id = ' . (int) $userId)->loadObject();
        $club = amuj_club_status($userId, (int) ($row->club_level_id ?? 0));
        $name = trim((string) ($row->name ?? '')) ?: 'مشتری';
        $mobile = preg_match('/^09\d{9}$/', (string) $row->username) ? $row->username : '';

        return [
            'real'       => true,
            'id'         => (int) $userId,
            'name'       => $name,
            'first'      => preg_split('/\s+/u', $name)[0],
            'phone'      => $mobile ? amuj_fa(substr($mobile, 0, 4) . ' *** ' . substr($mobile, -4)) : '',
            'mobile'     => amuj_fa($mobile),
            // com_auth stores "<mobile>@<host>" when the customer gave no email: not a real address
            'email'      => preg_match('/^09\d{9}@/', (string) $row->email) ? '' : (string) $row->email,
            'birthday'   => amuj_birthday_text((string) ($row->birthday ?? '')),
            'level'      => $club['level'],
            'next_level' => $club['next_level'],
            'progress'   => $club['progress'],
            'remaining'  => $club['remaining'],
            'wallet'     => $club['wallet'],
            'unread'     => (int) $db->setQuery('SELECT COUNT(*) FROM #__club_messages WHERE user_id = ' . (int) $userId . ' AND seen = 0')->loadResult(),
        ];
    }

    /**
     * Club level, progress to the next level (confirmed HikaShop orders, as the club plugin counts them) and wallet balance.
     * Without the club extension: empty level and zero wallet.
     */
    function amuj_club_status($userId, $levelId)
    {
        $db  = \Joomla\CMS\Factory::getDbo();
        $out = ['level' => '', 'next_level' => '', 'progress' => 100, 'remaining' => 0, 'wallet' => 0];
        $tables = $db->getTableList();
        if (!in_array($db->getPrefix() . 'club_levels', $tables, true)) {
            return $out;
        }

        $levels = $db->setQuery('SELECT id, level_name, level_sort, min_purchase_amount FROM #__club_levels WHERE state = 1 ORDER BY level_sort')->loadObjectList('id');
        $current = $levels[$levelId] ?? reset($levels) ?: null;
        $out['level'] = $current ? $current->level_name : '';

        $total = 0;
        if (function_exists('hikashop_config') || is_file(JPATH_ADMINISTRATOR . '/components/com_hikashop/helpers/helper.php')) {
            include_once JPATH_ADMINISTRATOR . '/components/com_hikashop/helpers/helper.php';
            $confirmed = hikashop_config()->get('order_confirmed_status', 'confirmed');
            $total = (float) $db->setQuery('SELECT SUM(o.order_full_price) FROM #__hikashop_order o JOIN #__hikashop_user hu ON hu.user_id = o.order_user_id'
                . ' WHERE hu.user_cms_id = ' . (int) $userId . ' AND o.order_type = "sale" AND o.order_status IN (' . $db->quote($confirmed) . ', "shipped")')->loadResult();
        }
        foreach ($levels as $level) {
            if ($current && $level->level_sort > $current->level_sort) {
                $out['next_level'] = $level->level_name;
                $out['remaining']  = max(0, (float) $level->min_purchase_amount - $total);
                $span = max(1, (float) $level->min_purchase_amount - (float) $current->min_purchase_amount);
                $out['progress']   = (int) max(0, min(100, round(($total - (float) $current->min_purchase_amount) / $span * 100)));
                break;
            }
        }

        $out['wallet'] = (float) $db->setQuery('SELECT SUM(currency_balance) FROM #__club_credits WHERE trans_type = 1 AND state = 1 AND user_id = ' . (int) $userId)->loadResult();

        return $out;
    }

    /** Club birthday "1370-05-12" (Persian calendar) -> "۱۲ مرداد ۱۳۷۰"; empty when not set */
    function amuj_birthday_text($birthday)
    {
        if (!preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $birthday, $m)) {
            return '';
        }
        $months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];

        return amuj_fa((int) $m[3]) . ' ' . ($months[(int) $m[2] - 1] ?? '') . ' ' . amuj_fa($m[1]);
    }

    /** Date in the Persian calendar, e.g. "۸ آبان ۱۴۰۵" (with $time: "۸ آبان ۱۴۰۵، ۱۶:۴۷") */
    function amuj_jdate($date, $time = false)
    {
        $ts = is_numeric($date) ? (int) $date : strtotime((string) $date);
        if (class_exists('IntlDateFormatter')) {
            $fmt = new IntlDateFormatter('fa_IR@calendar=persian', IntlDateFormatter::LONG, IntlDateFormatter::SHORT, 'Asia/Tehran',
                IntlDateFormatter::TRADITIONAL, $time ? 'd MMMM y، HH:mm' : 'd MMMM y');
            $out = $fmt->format($ts);
            if ($out) {
                return $out;
            }
        }

        return amuj_jalali_month($ts);
    }

    /** Current year of the Persian calendar (e.g. 1405) */
    function amuj_jyear()
    {
        if (class_exists('IntlDateFormatter')) {
            $fmt = new IntlDateFormatter('en_US@calendar=persian', IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'Asia/Tehran',
                IntlDateFormatter::TRADITIONAL, 'y');
            $y = (int) $fmt->format(time());
            if ($y > 1300) {
                return $y;
            }
        }

        return (int) date('Y') - 621;
    }

    /** "۲ ساعت پیش", "دیروز", "۳ روز پیش", older -> the date */
    function amuj_time_ago($date)
    {
        $ts   = is_numeric($date) ? (int) $date : strtotime((string) $date);
        $diff = time() - $ts;
        if ($diff < 3600) {
            return max(1, (int) floor($diff / 60)) > 1 ? amuj_fa((int) floor($diff / 60)) . ' دقیقه پیش' : 'همین الان';
        }
        if ($diff < 86400) {
            return amuj_fa((int) floor($diff / 3600)) . ' ساعت پیش';
        }
        if ($diff < 172800) {
            return 'دیروز';
        }
        if ($diff < 7 * 86400) {
            return amuj_fa((int) floor($diff / 86400)) . ' روز پیش';
        }

        return amuj_jdate($ts);
    }

    /** Club messages of the customer, newest first, in the shape of amuj_notifications() */
    function amuj_real_notifications($userId, $limit = 20)
    {
        $db = \Joomla\CMS\Factory::getDbo();
        if (!in_array($db->getPrefix() . 'club_messages', $db->getTableList(), true)) {
            return [];
        }
        $rows = $db->setQuery('SELECT id, created_on, message_subject, message, issystem, seen FROM #__club_messages'
            . ' WHERE user_id = ' . (int) $userId . ' AND (state IS NULL OR state <> 0) ORDER BY id DESC', 0, (int) $limit)->loadObjectList();
        $out = [];
        foreach ($rows as $r) {
            $subject = trim(strip_tags((string) $r->message_subject));
            if (!$r->issystem) {
                [$type, $label] = ['manual', 'گالری آموژ'];
            } elseif (mb_strpos($subject, 'سطح') !== false) {
                [$type, $label] = ['level', 'باشگاه مشتریان'];
            } else {
                [$type, $label] = ['credit', 'کیف پول'];
            }
            $out[] = [
                'id'     => (int) $r->id,
                'type'   => $type,
                'label'  => $label,
                'title'  => amuj_fa($subject),
                'text'   => amuj_fa(trim(strip_tags((string) $r->message))),
                'time'   => amuj_time_ago($r->created_on),
                'unread' => !$r->seen,
            ];
        }

        return $out;
    }

    /** Customer-facing status of a HikaShop order: [label, badge modifier, step in amuj_order_steps() (-1 cancelled)] */
    function amuj_order_state($status)
    {
        switch ($status) {
            case 'created':
            case 'pending':
                return ['در انتظار پرداخت', 'active', 0];
            case 'confirmed':
                return ['در حال آماده‌سازی', 'active', 2];
            case 'shipped':
                return ['ارسال شده', 'shipped', 3];
            case 'delivered':
                return ['تحویل شده', 'done', 4];
            case 'cancelled':
                return ['لغو شده', 'cancelled', -1];
            case 'refunded':
                return ['بازپرداخت شده', 'cancelled', -1];
        }

        return [function_exists('hikashop_orderStatus') ? hikashop_orderStatus($status) : $status, 'active', 1];
    }

    /** Orders of the signed-in customer (HikaShop sales), newest first */
    function amuj_customer_orders($userId, $limit = 0)
    {
        $db = \Joomla\CMS\Factory::getDbo();

        return $db->setQuery('SELECT o.order_id, o.order_number, o.order_status, o.order_created, o.order_full_price, o.order_payment_method,'
            . ' o.order_shipping_id FROM #__hikashop_order o JOIN #__hikashop_user hu ON hu.user_id = o.order_user_id'
            . ' WHERE hu.user_cms_id = ' . (int) $userId . ' AND o.order_type = "sale" ORDER BY o.order_created DESC, o.order_id DESC',
            0, (int) $limit)->loadObjectList();
    }

    /** Photos of the products of some orders: order_id => [image url, ...] (first variant colour photo) */
    function amuj_order_thumbs(array $orderIds)
    {
        $orderIds = array_filter(array_map('intval', $orderIds));
        if (!$orderIds) {
            return [];
        }
        $db = \Joomla\CMS\Factory::getDbo();
        $rows = $db->setQuery('SELECT op.order_id, op.product_id, IF(p.product_parent_id > 0, p.product_parent_id, p.product_id) AS main'
            . ' FROM #__hikashop_order_product op LEFT JOIN #__hikashop_product p ON p.product_id = op.product_id'
            . ' WHERE op.order_id IN (' . implode(',', $orderIds) . ') AND op.order_product_quantity > 0 AND op.product_id > 0'
            . ' AND op.order_product_option_parent_id = 0')->loadObjectList();
        $mains = amuj_hika_products(array_values(array_unique(array_map(function ($r) { return (int) $r->main; }, $rows))));
        $out = [];
        foreach ($rows as $r) {
            $p = $mains[(int) $r->main] ?? null;
            if (!$p) {
                continue;
            }
            $img = $p['img'];
            foreach ($p['variants'] as $key => $v) {
                if ((int) $v[0] === (int) $r->product_id) {
                    $img = $p['color_images'][(int) explode('|', $key)[0]] ?? $img;
                    break;
                }
            }
            $out[(int) $r->order_id][] = amuj_img($img, 200);
        }

        return $out;
    }

    /** Order page of the customer panel (menu item "orders") */
    function amuj_order_url($orderId)
    {
        static $itemId = null;
        if ($itemId === null) {
            $item = \Joomla\CMS\Factory::getApplication()->getMenu('site')->getItems(['menutype', 'alias'], ['amuj-pages', 'orders'], true);
            $itemId = $item ? (int) $item->id : 0;
        }

        return \Joomla\CMS\Router\Route::_('index.php?option=com_hikashop&ctrl=order&task=show&cid=' . (int) $orderId . ($itemId ? '&Itemid=' . $itemId : ''));
    }

    /** Customer-panel pages: a guest is sent to the mobile login and comes back afterwards */
    function amuj_panel_guard()
    {
        $app = \Joomla\CMS\Factory::getApplication();
        if ($app->getIdentity()->guest) {
            $app->redirect(amuj_login_url());
        }
    }

    /** data-url / data-token of a "mark all as read" button for a real customer (empty for the demo) */
    function amuj_readall_attrs()
    {
        $user = amuj_current_user();
        if (empty($user['real'])) {
            return '';
        }

        return ' data-url="' . htmlspecialchars(AMUJ_BASE . 'index.php?option=com_ajax&group=ajax&plugin=amujcontact&format=raw') . '"'
            . ' data-token="' . \Joomla\CMS\Session\Session::getFormToken() . '"';
    }
}
