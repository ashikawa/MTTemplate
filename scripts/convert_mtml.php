#! /usr/bin/php
<?php
define('STATIC_DIR',   realpath(dirname(__FILE__) . "/../static/"));
define('TEMPLATE_DIR', realpath(dirname(__FILE__) . "/../theme/template/templates/"));

require_once './lib/HtmlConverter.php';

// html -> mtml
$vars = array(
    '<meta charset="UTF-8">' => '<meta charset="<$mt:PublishCharset$>">',
    '<html lang="ja">'       => '<html lang="<$mt:BlogLanguage$>">',
    '<!--#include virtual="/inc/header.html" -->'  => '<$mt:Include identifier="header"$>',
    '<!--#include virtual="/inc/footer.html" -->'  => '<$mt:Include identifier="footer"$>',
    '<!--#include virtual="/inc/sidebar.html" -->' => '<$mt:Include identifier="sidebar"$>',
);

$modifier = array(
    array('<mt:Unless replace="http://example.com/","/">', '</mt:Unless>'),
    array('<mt:Unless regex_replace="/\s*\n+/g","\n">', '</mt:Unless>'),
);

$static = array(
    '/inc/footer.html'  => 'footer',
    '/inc/sidebar.html' => 'sidebar',
);

foreach ($static as $from => $to) {
    $converter = new HtmlConverter($from);
    $converter->replace($vars)
        ->write("/$to.mtml");
}

/**
 * 個別モジュール
 */
$converter = new HtmlConverter("/inc/header.html");

$modules = array(
    'h1'    => '<$mt:BlogName encode_html="1"$>',
);

$converter->replace($vars)
    ->processModules($modules)
    ->write("/header.mtml");

$converter = new HtmlConverter("/index.html");

$modules = array(
    'h1'     => '<$mt:EntryTitle$>',
    'p'      => '<$mt:EntryExcerpt$>',
    '[href]' => '<$mt:EntryPermalink encode_html=\'1\'$>',
);

$converter->replace($vars)
    ->clip(array('<article>', '</article>'))
    ->wrap(array(array('    <article>', '</article>')))
    ->processModules($modules)
    ->write("/entry_summary.mtml");

/**
 * HTML全体
 */
$converter = new HtmlConverter("/index.html");

$modules = array(
    'title' => '<$mt:BlogName encode_html="1"$>',
    'meta[name=description]' => array(
        'pattern' => '%<meta name="description" content=".*?">%si',
        'replace' => '<meta name="description" content="<$mt:BlogDescription$>">',
    ),
    '.contents' => array(
        'pattern' => array('<div class="contents">', '</div><!-- /.contents -->'),
        'inner'   => <<<EOT
<mt:Entries>
<\$mt:Include identifier="entry_summary"\$>
</mt:Entries>
EOT
    ),
);

$converter->replace($vars)
    ->processModules($modules)
    ->wrap($modifier)
    ->write("/main_index.mtml");

$converter = new HtmlConverter("/category/index.html");

$modules = array(
    'title' => '<$mt:CategoryLabel encode_html="1"$> | <$mt:BlogName encode_html="1"$>',
    'meta[name=description]' => array(
        'pattern' => '%<meta name="description" content=".*?">%si',
        'replace' => '<meta name="description" content="<$mt:CategoryDescription$>">',
    ),
    '.contents' => array(
        'pattern' => array('<div class="contents">', '</div><!-- /.contents -->'),
        'inner'   => <<<EOT
<mt:Entries>
<\$mt:Include identifier="entry_summary"\$>
</mt:Entries>
EOT
    ),
);

$converter->replace($vars)
    ->processModules($modules)
    ->wrap($modifier)
    ->write("/category_entry_listing.mtml");

$converter = new HtmlConverter("/category/pages.html");

$modules = array(
    'title' => '<$mt:EntryTitle encode_html="1"$> | <$mt:BlogName encode_html="1"$>',
    'meta[name=description]' => array(
        'pattern' => '%<meta name="description" content=".*?">%si',
        'replace' => '<meta name="description" content="<$mt:EntryExcerpt$>">',
    ),
    'meta[name=keywords]' => array(
        'pattern' => '%<meta name="keywords" content=".*?">%si',
        'replace' => '<meta name="keywords" content="<$mt:EntryKeywords$>">',
    ),
    'h1' => '<$mt:EntryTitle encode_html="1"$>',
    '.contents' => array(
        'pattern' => array('<div class="entry-body">', '</div><!-- /.entry-body -->'),
        'inner'   => '<$mt:EntryBody$>'
    ),
);

$converter->replace($vars)
    ->processModules($modules)
    ->wrap($modifier)
    ->write("/entry.mtml");

$converter = new HtmlConverter("/pages/page1.html");

$modules = array(
    'title' => '<$mt:EntryTitle encode_html="1"$> | <$mt:BlogName encode_html="1"$>',
    'meta[name=description]' => array(
        'pattern' => '%<meta name="description" content=".*?">%si',
        'replace' => '<meta name="description" content="<$mt:MTPageExcerpt$>">',
    ),
    'meta[name=keywords]' => array(
        'pattern' => '%<meta name="keywords" content=".*?">%si',
        'replace' => '<meta name="keywords" content="<$mt:PageKeywords$>">',
    ),
    'h1' => '<$mt:EntryTitle encode_html="1"$>',
    '.contents' => array(
        'pattern' => array('<div class="entry-body">', '</div><!-- /.entry-body -->'),
        'inner'   => '<$mt:EntryBody$>'
    ),
);

$converter->replace($vars)
    ->processModules($modules)
    ->wrap($modifier)
    ->write("/page.mtml");
