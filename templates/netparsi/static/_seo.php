<?php

/**
 * Head tags for search engines and link previews, written by the template (index.php) for every page:
 * meta description, canonical link, Open Graph / Twitter card, and JSON-LD structured data.
 *
 * Pages add their own data before the template runs (component overrides):
 *   amuj_seo(['description' => ..., 'image' => ..., 'type' => 'product', 'schema' => [...], 'robots' => ..., 'canonical' => ...]);
 * Everything else falls back to the page title, the site description (global variable "site_description")
 * and the shop's share image.
 */

defined('_JEXEC') or die;

if (!function_exists('amuj_seo')) {

    /** Collect head data for this page (later calls add to / replace earlier values; 'schema' entries add up) */
    function amuj_seo(?array $data = null)
    {
        static $seo = ['schema' => []];
        if ($data !== null) {
            $schema = $data['schema'] ?? null;
            unset($data['schema']);
            $seo = array_merge($seo, array_filter($data, function ($v) { return $v !== null && $v !== ''; }));
            if ($schema) {
                $seo['schema'][] = $schema;
            }
        }

        return $seo;
    }

    /** Plain text for a meta description (no tags, one line, at most $max characters) */
    function amuj_seo_text($html, $max = 160)
    {
        $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8')));
        if (mb_strlen($text) > $max) {
            $text = rtrim(mb_substr($text, 0, $max - 1), ' ،,.') . '…';
        }

        return $text;
    }

    /** Absolute URL of a site path or URL */
    function amuj_seo_abs($url)
    {
        if ($url === '' || preg_match('#^https?://#', $url)) {
            return $url;
        }
        $base = \Joomla\CMS\Uri\Uri::getInstance()->toString(['scheme', 'host', 'port']);

        return $url[0] === '/' ? $base . $url : rtrim(\Joomla\CMS\Uri\Uri::root(), '/') . '/' . ltrim($url, '/');
    }

    /** Write the tags into the document (called once by index.php) */
    function amuj_seo_render(\Joomla\CMS\Document\HtmlDocument $doc)
    {
        $seo      = amuj_seo();
        $site     = \Joomla\CMS\Factory::getApplication()->get('sitename');
        $contact  = amuj_contact();
        $root     = rtrim(\Joomla\CMS\Uri\Uri::root(), '/') . '/';
        $title    = trim((string) $doc->getTitle()) ?: $site;
        $desc     = $seo['description'] ?? (trim((string) $doc->getDescription()) ?: amuj_seo_text(amuj_var('site_description',
            'گالری آموژ؛ فروشگاه لباس زنانه با کالکشن‌های مانتو، کت، پالتو و پیراهن. خرید آنلاین با ارسال به سراسر ایران و ۱۴ روز مهلت تعویض.')));
        $image    = amuj_seo_abs($seo['image'] ?? 'images/products/cream/1.jpg');
        $current  = \Joomla\CMS\Uri\Uri::getInstance();
        $canonical = $seo['canonical'] ?? $current->toString(['scheme', 'host', 'port', 'path']);

        $doc->setGenerator('');
        $doc->setDescription($desc);
        if (!empty($seo['robots'])) {
            $doc->setMetaData('robots', $seo['robots']);
        }
        $robots = (string) $doc->getMetaData('robots');
        if (stripos($robots, 'noindex') === false) {
            $doc->addHeadLink(htmlspecialchars(amuj_seo_abs($canonical)), 'canonical');
        }

        // Link previews (Telegram, WhatsApp, Instagram, X...)
        $og = [
            'og:site_name' => $site,
            'og:locale'    => 'fa_IR',
            'og:type'      => ($seo['type'] ?? '') === 'product' ? 'product' : 'website',
            'og:title'     => $title,
            'og:description' => $desc,
            'og:url'       => amuj_seo_abs($canonical),
            'og:image'     => $image,
        ];
        foreach ($seo['og'] ?? [] as $k => $v) {
            $og[$k] = $v;
        }
        foreach ($og as $property => $content) {
            if ($content !== '' && $content !== null) {
                $doc->setMetaData($property, (string) $content, 'property');
            }
        }
        $doc->setMetaData('twitter:card', 'summary_large_image');

        // Structured data: the shop on every page, plus the page's own (product, breadcrumb...)
        $graph = [[
            '@type'  => 'ClothingStore',
            '@id'    => $root . '#store',
            'name'   => $site,
            'url'    => $root,
            'logo'   => $root . 'images/amuj-logo-mail.png',
            'image'  => amuj_seo_abs('images/products/cream/1.jpg'),
            'telephone' => $contact['tel'] ?? '',
            'address' => ['@type' => 'PostalAddress', 'streetAddress' => $contact['address'] ?? '', 'addressLocality' => 'تهران', 'addressCountry' => 'IR'],
            'sameAs' => array_values(array_filter([$contact['instagram'] ?? ''])),
        ], [
            '@type' => 'WebSite',
            '@id'   => $root . '#website',
            'url'   => $root,
            'name'  => $site,
            'inLanguage' => 'fa-IR',
            'publisher' => ['@id' => $root . '#store'],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => ['@type' => 'EntryPoint', 'urlTemplate' => $root . 'index.php?page=search&q={search_term_string}'],
                'query-input' => 'required name=search_term_string',
            ],
        ]];
        foreach ($seo['schema'] as $node) {
            $graph[] = $node;
        }
        $doc->addCustomTag('<script type="application/ld+json">'
            . json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . '</script>');
    }
}
