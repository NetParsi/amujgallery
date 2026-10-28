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
 * answers {"ok": bool, "message": string}.
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

        if (!Session::checkToken()) {
            $this->respond(false, 'زمان صفحه تمام شده است؛ صفحه را تازه کنید و دوباره بفرستید.');
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

    private function respond(bool $ok, string $message): void
    {
        $app = $this->getApplication();
        $app->setHeader('Content-Type', 'application/json; charset=utf-8', true);
        $app->sendHeaders();
        echo json_encode(['ok' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE);
        $app->close();
    }
}
