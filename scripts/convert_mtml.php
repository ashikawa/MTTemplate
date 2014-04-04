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
    array('<mt:Unless regex_replace="/\s*\n+/g","\n">',    '</mt:Unless>'),
);

$static = array(
    '/inc/footer.html'  => 'footer',
    '/inc/sidebar.html' => 'sidebar',
);

foreach ($static as $from => $to) {
    $converter = new HtmlConverter(STATIC_DIR . $from);
    $converter->replace($vars)
        ->write(TEMPLATE_DIR . "/$to.mtml");
}

/**
 * 個別モジュール
 */
$converter = new HtmlConverter(STATIC_DIR . "/inc/header.html");

$modules = array(
    'h1'    => '<$mt:BlogName encode_html="1"$>',
);

$converter->replace($vars)
    ->processModules($modules)
    ->write(TEMPLATE_DIR . "/header.mtml");

$converter = new HtmlConverter(STATIC_DIR . "/index.html");

$modules = array(
    'h1'     => '<$mt:EntryTitle$>',
    'p'      => '<$mt:EntryExcerpt$>',
    '[href]' => '<$mt:EntryPermalink encode_html=\'1\'$>',
);

$converter->replace($vars)
    ->clip(array('<article>', '</article>'))
    ->wrap(array(array('    <article>', '</article>')))
    ->processModules($modules)
    ->write(TEMPLATE_DIR . "/entry_summary.mtml");

/**
 * HTML全体
 */
$converter = new HtmlConverter(STATIC_DIR . "/index.html");

$modules = array(
    'title' => '<$mt:BlogName encode_html="1"$>',
    'meta[name=description]' => '<$mt:BlogDescription$>',
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
    ->write(TEMPLATE_DIR . "/main_index.mtml");

$converter = new HtmlConverter(STATIC_DIR . "/category/index.html");

$modules = array(
    'title' => '<$mt:CategoryLabel encode_html="1"$> | <$mt:BlogName encode_html="1"$>',
    'meta[name=description]' => '<$mt:CategoryDescription$>',
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
    ->write(TEMPLATE_DIR . "/category_entry_listing.mtml");

$converter = new HtmlConverter(STATIC_DIR . "/category/pages.html");

$modules = array(
    'title' => '<$mt:EntryTitle encode_html="1"$> | <$mt:BlogName encode_html="1"$>',
    'h1'    => '<$mt:EntryTitle encode_html="1"$>',
    'meta[name=description]' => '<$mt:EntryExcerpt$>',
    'meta[name=keywords]'    => '<$mt:EntryKeywords$>',
    '.contents' => array(
        'pattern' => array('<div class="entry-body">', '</div><!-- /.entry-body -->'),
        'inner'   => '<$mt:EntryBody$>'
    ),
);

$converter->replace($vars)
    ->processModules($modules)
    ->wrap($modifier)
    ->write(TEMPLATE_DIR . "/entry.mtml");

$converter = new HtmlConverter(STATIC_DIR . "/pages/page1.html");

$modules = array(
    'title' => '<$mt:EntryTitle encode_html="1"$> | <$mt:BlogName encode_html="1"$>',
    'h1'    => '<$mt:EntryTitle encode_html="1"$>',
    'meta[name=description]' => '<$mt:MTPageExcerpt$>',
    'meta[name=keywords]'    => '<$mt:PageKeywords$>',
    '.contents' => array(
        'pattern' => array('<div class="entry-body">', '</div><!-- /.entry-body -->'),
        'inner'   => '<$mt:EntryBody$>'
    ),
);

$converter->replace($vars)
    ->processModules($modules)
    ->wrap($modifier)
    ->write(TEMPLATE_DIR . "/page.mtml");
