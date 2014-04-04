<?php
class HtmlConverter
{
    protected $_html = null;

    public function __construct($path)
    {
        if ($path) {
            $this->load($path);
        }
    }

    public function load($path)
    {
        $html = file_get_contents($path);

        if ($html === false) {
            throw new Exception("File Read Error `$path`", 1);
        }

        $this->_html = $html;

        return $this;
    }

    public function write($path)
    {
        $html = $this->_html;

        $ret = file_put_contents($path, $html);

        if ($ret === false) {
            throw new Exception("File Write Error `$path`", 1);
        }

        return $this;
    }

    public function wrap($tags)
    {
        foreach ($tags as $key => $element) {
            $this->prepend($element[0])
                ->append($element[1]);
        }

        return $this;
    }

    /**
     * 単純な定数の置換
     */
    public function replace($replace)
    {
        $html = $this->_html;

        foreach ($replace as $_search => $_replace) {
            $html = str_replace($_search, $_replace, $html);
        }

        $this->_html = $html;

        return $this;
    }

    public function append($text)
    {
        $this->_html = $this->_html . PHP_EOL . $text;

        return $this;
    }

    public function prepend($text)
    {
        $this->_html = $text . PHP_EOL . $this->_html;

        return $this;
    }

    public function clip($pattern)
    {
        if (is_array($pattern)) {
            $pattern = sprintf('/%s(.*?)%s/si',
                preg_quote($pattern[0], '/'),
                preg_quote($pattern[1], '/'));
        }

        $ret = preg_match($pattern, $this->_html, $matches);

        if (!$ret) {
            throw new Exception("Error replacement `{$pattern}`", 1);
        }

        $this->_html  = $matches[1];

        return $this;
    }

    /**
     * 個別モジュールの切り出し
     */
    public function processModules($replace)
    {
        $html = $this->_html;

        foreach ($replace as $name => $element) {

            if (is_string($element)) {
                $element = $this->_stringToPattern($name, $element);
            }

            $html = $this->_process($element, $html);
        }

        $this->_html = $html;

        return $this;
    }

    protected function _stringToPattern($name, $element)
    {
        // tag.class1.class2
        $explode = explode('.', $name);

        if (count($explode) > 1) {
            // class
            $tag     = array_shift($explode);
            $classes = implode(' ', $explode);

            return array(
                'pattern' => array("<{$tag} class=\"{$classes}\">", "</{$tag}>"),
                'inner'   => $element,
            );
        }

        // [attr] -> attr="element"
        $ret = preg_match('/^\[([a-z0-9\-\_\:]*?)\]$/i', $name, $matches);

        if ($ret) {
            // attr
            return array(
                'pattern' => sprintf('/%s=".*?"/si', preg_quote($matches[1], '/')),
                'replace' => $matches[1] . '="' . $element . '"',
            );
        }

        $ret = preg_match('/^meta\[([a-z0-9\-\_\:]*?)=([a-z]*?)\]$/i', $name, $matches);

        if ($ret) {
            // attr
            return array(
                'pattern' => sprintf('/<meta %s="%s" content=".*?">/si',
                    preg_quote($matches[1], '/'),
                    preg_quote($matches[2], '/')),
                'replace' => sprintf(
                    '<meta %s="%s" content="%s">',
                    $matches[1],
                    $matches[2],
                    $element),
            );
        }

        // tag
        return array(
            'pattern' => array("<{$name}>", "</{$name}>"),
            'inner'   => $element,
        );
    }

    /**
     * 置換処理の実行
     * エラーアサーション
     */
    protected function _process($options, $html)
    {
        $pattern = $options['pattern'];

        if ($options['replace']) {
            $replace = $options['replace'];
        } else {
            $replace = $pattern[0] . $options['inner'] . $pattern[1];
        }

        $limit   = -1; // replace count: infinity

        if (is_array($pattern)) {
            $pattern = sprintf( '/%s.*?%s/si',
                preg_quote($pattern[0], '/'),
                preg_quote($pattern[1], '/'));
        }

        if (is_callable($replace)) {
            $html = preg_replace_callback($pattern, $replace, $html, $limit, $count);
        } else {
            $html = preg_replace($pattern, $replace, $html, $limit, $count);
        }

        if ($count === 0) {
            throw new Exception("Error replacement `{$pattern}`", 1);
        }

        return $html;
    }
}
