<?php

/**
 * @package     Joomla.Site
 * @subpackage  Templates.netparsi
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

use Joomla\CMS\Helper\AuthenticationHelper;
use Joomla\CMS\Router\Route;

JHtml::_('jquery.framework');
//JHtml::_('behavior.keepalive');

/** @var Joomla\CMS\Document\HtmlDocument $this */

$app   = Factory::getApplication();
$input = $app->getInput();
$wa    = $this->getWebAssetManager();

$user = JFactory::getUser();
$params = $app->getTemplate(true)->params;
$menu = $app->getMenu();
$active = $menu->getActive();

$pageparams = $menu->getParams( $active->id );
$pageclass = $pageparams->get( 'pageclass_sfx' );

// Detecting Active Variables
$option   = $input->getCmd('option', '');
$view     = $input->getCmd('view', '');
$layout   = $input->getCmd('layout', '');
$task     = $input->getCmd('task', '');
$itemid   = $input->getCmd('Itemid', '');
$sitename = htmlspecialchars($app->get('sitename'), ENT_QUOTES, 'UTF-8');
$menu     = $app->getMenu()->getActive();
$pageclass = $menu !== null ? $menu->getParams()->get('pageclass_sfx', '') : '';

$extraButtons     = AuthenticationHelper::getLoginButtons('form-login');
// Enable assets
$wa
    ->usePreset('template.uikit-'.$this->direction)
    ->usePreset('template.fa')
    ->usePreset('template.netparsi-'.$this->direction)
    ->useStyle('template.slider')
;
$netparsi = "<a href='https://netparsi.com' class='uk-text-white font f400 netparsi link' target='_blank' rel='nofollow'>" . JTEXT::sprintf('NETPARSI') . "</a>";

$this->setMetaData('viewport', 'width=device-width, initial-scale=1');

// Favicons
$this->addHeadLink(HTMLHelper::_('image', 'images/favicon/favicon-any.ico', '', [], false, 1), 'icon', 'rel', ['sizes' => 'any']);
$this->addHeadLink(HTMLHelper::_('image', 'images/favicon/favicon.ico', '', [], false, 1), 'icon', 'rel', ['sizes' => '16x16 32x32 64x64']);
$this->addHeadLink(HTMLHelper::_('image', 'images/favicon/favicon-196.png', '', [], false, 1), 'image/png', 'type', ['sizes' => '196x196']);
//$this->addHeadLink(HTMLHelper::_('image', 'images/favicon.ico', '', [], false, 1), 'alternate icon', 'rel', ['type' => 'image/vnd.microsoft.icon']);
//$this->addHeadLink(HTMLHelper::_('image', 'images/favicon.svg', '', [], false, 1), 'mask-icon', 'rel', ['color' => '#000']);
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
    <jdoc:include type="metas" />
    <jdoc:include type="styles" />
    <jdoc:include type="scripts" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">


<!--    <link rel="icon" type="image/png" sizes="196x196" href="/favicons/favicon-192.png"/>-->
<!--    <link rel="icon" type="image/png" sizes="160x160" href="/favicons/favicon-160.png"/>-->
<!--    <link rel="icon" type="image/png" sizes="96x96" href="/favicons/favicon-96.png"/>-->
<!--    <link rel="icon" type="image/png" sizes="64x64" href="/favicons/favicon-64.png"/>-->
<!--    <link rel="icon" type="image/png" sizes="32x32" href="/favicons/favicon-32.png"/>-->
<!--    <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16.png"/>-->
<!--    <link rel="apple-touch-icon" href="/favicons/favicon-57.png"/>-->
<!--    <link rel="apple-touch-icon" sizes="114x114" href="/favicons/favicon-114.png"/>-->
<!--    <link rel="apple-touch-icon" sizes="72x72" href="/favicons/favicon-72.png"/>-->
<!--    <link rel="apple-touch-icon" sizes="144x144" href="/favicons/favicon-144.png"/>-->
<!--    <link rel="apple-touch-icon" sizes="60x60" href="/favicons/favicon-60.png"/>-->
<!--    <link rel="apple-touch-icon" sizes="120x120" href="/favicons/favicon-120.png"/>-->
<!--    <link rel="apple-touch-icon" sizes="76x76" href="/favicons/favicon-76.png"/>-->
<!--    <link rel="apple-touch-icon" sizes="152x152" href="/favicons/favicon-152.png"/>-->
<!--    <link rel="apple-touch-icon" sizes="180x180" href="/favicons/favicon-180.png"/>-->
</head>

<body class="site <?php echo $option
    . ' view-' . $view
    . ($layout ? ' layout-' . $layout : ' no-layout')
    . ($task ? ' task-' . $task : ' no-task')
    . ($itemid ? ' itemid-' . $itemid : '')
    . ($pageclass ? ' ' . $pageclass : '')
    . ($this->direction == 'rtl' ? ' rtl' : '');
?>">
<main class="uk-background-muted uk-height-viewport uk-flex uk-flex-middle">
    <article class="uk-margin-large-bottom uk-flex-1">
        <div class="uk-padding-small uk-padding-remove-vertical uk-width-1-1 uk-width-1-3@m uk-margin-auto">
            <div class="uk-flex uk-flex-center uk-flex-column uk-flex-middle uk-margin-medium-bottom">
                <a href="<?php echo Uri::base(); ?>" class="uk-flex uk-margin-small-bottom" title="<?php echo $sitename; ?>" target="_self">
                    <?php if ($app->get('offline_image')) : ?>
                        <?php echo HTMLHelper::_('image', $app->get('offline_image'), $sitename, [], false, 0); ?>
                    <?php else: ?>
                        <img src="<?php echo (HTMLHelper::cleanImageURL($params->get('logo')))->url; ?>" alt="" width="133" height="" class="logo logoDark">
                    <?php endif; ?>
                </a>
            </div>
            <div class="uk-background-white uk-padding uk-width-1-1 uk-width-1-1@m uk-margin-bottom uk-border-rounded uk-overflow-hidden uk-position-relative uk-border">
                <jdoc:include type="message" />
                <form action="<?php echo Route::_('index.php', true); ?>" method="post" id="form-login" class="uk-form-stacked">
                    <?php if ($app->get('display_offline_message', 1) == 1 && str_replace(' ', '', $app->get('offline_message')) != '') : ?>
                        <p class="uk-text-secondary uk-margin-medium-bottom uk-text-small uk-text-center f500"><?php echo $app->get('offline_message'); ?></p>
                    <?php elseif ($app->get('display_offline_message', 1) == 2) : ?>
                        <p class="uk-text-secondary uk-margin-medium-bottom uk-text-small uk-text-center f500"><?php echo Text::_('JOFFLINE_MESSAGE'); ?></p>
                    <?php endif; ?>
                    <div class="uk-child-width-1-1 uk-child-width-1-2@m uk-grid-small" data-uk-grid>
                        <div>
                            <label for="username" class="uk-form-label"><?php echo Text::_('JGLOBAL_USERNAME'); ?></label>
                            <div>
                                <input name="username" class="uk-input uk-form-large form-control" id="username" type="text">
                            </div>
                        </div>
                        <div>
                            <label for="password" class="uk-form-label"><?php echo Text::_('JGLOBAL_PASSWORD'); ?></label>
                            <div>
                                <input name="password" class="uk-input uk-form-large form-control" id="password" type="password">
                            </div>
                        </div>
                        <?php foreach ($extraButtons as $button) :
                            $dataAttributeKeys = array_filter(array_keys($button), function ($key) {
                                return substr($key, 0, 5) == 'data-';
                            });
                            ?>
                            <div class="uk-width-1-1 mod-login__submit form-group">
                                <button type="button" class="uk-button uk-button-large uk-button-primary <?php echo $button['class'] ?? '' ?>"
                                <?php foreach ($dataAttributeKeys as $key) : ?>
                                    <?php echo $key ?>="<?php echo $button[$key] ?>"
                                <?php endforeach; ?>
                                <?php if ($button['onclick']) : ?>
                                    onclick="<?php echo $button['onclick'] ?>"
                                <?php endif; ?>
                                title="<?php echo Text::_($button['label']) ?>"
                                id="<?php echo $button['id'] ?>"
                                >
                                <?php if (!empty($button['icon'])) : ?>
                                    <span class="<?php echo $button['icon'] ?>"></span>
                                <?php elseif (!empty($button['image'])) : ?>
                                    <?php echo $button['image']; ?>
                                <?php elseif (!empty($button['svg'])) : ?>
                                    <?php echo $button['svg']; ?>
                                <?php endif; ?>
                                <?php echo Text::_($button['label']) ?>
                                </button>
                            </div>
                        <?php endforeach; ?>
                        <div class="uk-width-1-1">
                            <button type="submit" name="Submit" class="uk-button uk-button-large uk-button-primary"><?php echo Text::_('JLOGIN'); ?></button>
                        </div>

                        <input type="hidden" name="option" value="com_users">
                        <input type="hidden" name="task" value="user.login">
                        <input type="hidden" name="return" value="<?php echo base64_encode(Uri::base()); ?>">
                        <?php echo HTMLHelper::_('form.token'); ?>
                    </div>
                </form>
            </div>
        </div>
    </article>
</main>
</body>
</html>