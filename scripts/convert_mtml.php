#! /usr/bin/php
<?php
define('STATIC_DIR',   realpath(dirname(__FILE__) . "/../static/"));
define('TEMPLATE_DIR', realpath(dirname(__FILE__) . "/../theme/template/templates/"));

/**
 * @see http://nplll.com/archives/2011/10/php_simple_html_dom_parser.php
 */
require_once './lib/simple_html_dom.php';

function file_get_html_pretty($path)
{
    $dom = file_get_html(STATIC_DIR . $path, false, null, -1, -1, true, true, DEFAULT_TARGET_CHARSET, false, DEFAULT_BR_TEXT);

    // 共通処理
    if ($dom->getElementByTagName('html')) {
        $dom->getElementByTagName('html')->lang = '<$mt:BlogLanguage$>';
    }

    if ($dom->find('meta[charset]', 0)) {
        $dom->find('meta[charset]', 0)->charset = '<$mt:PublishCharset$>';
    }

    return $dom;
}

function add_modifier($dom)
{
    return implode(PHP_EOL, array(
        '<mt:Unless regex_replace="/\s*\n+/g","\n">',
        '<mt:Unless replace="http://example.com/","/">',
        $dom,
        '</mt:Unless>',
        '</mt:Unless>',
    ));
}

function output_file($path, $dom)
{
    $ssi = array(
        '<!--#include virtual="/inc/header.html" -->'  => '<$mt:Include identifier="header"$>',
        '<!--#include virtual="/inc/footer.html" -->'  => '<$mt:Include identifier="footer"$>',
        '<!--#include virtual="/inc/sidebar.html" -->' => '<$mt:Include identifier="sidebar"$>',
    );

    $dom = str_ireplace(array_keys($ssi), array_values($ssi), $dom);

    return file_put_contents(TEMPLATE_DIR . $path, $dom);
}

/**
 * 共通パーツ
 */
$dom = file_get_html_pretty("/inc/footer.html");
output_file("/footer.mtml", $dom);

$dom = file_get_html_pretty("/inc/sidebar.html");
output_file("/sidebar.mtml", $dom);

$dom = file_get_html_pretty("/inc/header.html");
$dom->getElementByTagName('h1')->innertext = '<$mt:BlogName encode_html="1"$>';
output_file("/header.mtml", $dom);

/**
 * アーカイブテンプレート
 */
$dom = file_get_html_pretty("/index.html");
$dom->find('article h1', 0)->innertext  = '<$mt:EntryTitle$>';
$dom->find('article p',  0)->innertext  = '<$mt:EntryExcerpt$>';
$dom->find('article a[href]',  0)->href = '<$mt:EntryPermalink encode_html=\'1\'$>';

$article = $dom->find('article',  0);
output_file("/entry_summary.mtml", $article);

$dom->getElementByTagName('title')->innertext    = '<$mt:BlogName encode_html="1"$>';
$dom->find('meta[name=description]', 0)->content = '<$mt:BlogDescription$>';
$dom->find('div.contents', 0)->innertext = <<<EOT
<mt:Entries>
<\$mt:Include identifier="entry_summary"\$>
</mt:Entries>
EOT;
$dom = add_modifier($dom);
output_file("/main_index.mtml", $dom);

$dom = file_get_html_pretty("/category/index.html");
$dom->getElementByTagName('title')->innertext    = '<$mt:CategoryLabel encode_html="1"$> | <$mt:BlogName encode_html="1"$>';
$dom->find('meta[name=description]', 0)->content = '<$mt:CategoryDescription$>';
$dom->find('div.contents', 0)->innertext = <<<EOT
<mt:Entries>
<\$mt:Include identifier="entry_summary"\$>
</mt:Entries>
EOT;
$dom = add_modifier($dom);
output_file("/category_entry_listing.mtml", $dom);

/**
 * 記事テンプレート
 */
$dom = file_get_html_pretty("/category/pages.html");
$dom->getElementByTagName('title')->innertext = '<$mt:EntryTitle encode_html="1"$> | <$mt:BlogName encode_html="1"$>';
$dom->getElementByTagName('h1')->innertext    = '<$mt:EntryTitle encode_html="1"$>';
$dom->find('meta[name=description]', 0)->content = '<$mt:EntryExcerpt$>';
$dom->find('meta[name=keywords]',    0)->content = '<$mt:EntryKeywords$>';
$dom->find('div.entry-body', 0)->innertext = '<$mt:EntryBody$>';
$dom = add_modifier($dom);
output_file("/entry.mtml", $dom);

$dom = file_get_html_pretty("/pages/page1.html");
$dom->getElementByTagName('title')->innertext = '<$mt:EntryTitle encode_html="1"$> | <$mt:BlogName encode_html="1"$>';
$dom->getElementByTagName('h1')->innertext    = '<$mt:EntryTitle encode_html="1"$>';
$dom->find('meta[name=description]', 0)->content = '<$mt:MTPageExcerpt$>';
$dom->find('meta[name=keywords]',    0)->content = '<$mt:PageKeywords$>';
$dom->find('div.entry-body', 0)->innertext = '<$mt:EntryBody$>';
$dom = add_modifier($dom);
output_file("/page.mtml", $dom);
