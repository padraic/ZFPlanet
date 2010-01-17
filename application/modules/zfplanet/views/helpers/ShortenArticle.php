<?php

class Zfplanet_View_Helper_ShortenArticle extends Zend_View_Helper_Abstract
{

    public function shortenArticle($content, $length = 2500, $encoding = null)
    {
        
        if (is_null($encoding)) $encoding = $this->view->getEncoding();
        $realLength = iconv_strlen($content, $encoding);
        if ($realLength <= $length) return $content;
        $content = iconv_substr($content, 0, $length, $encoding);
        return $content . '<em>[...]</em><p style="margin-bottom:0;">'
        . '<br/><em>Content was truncated. Another ' . ($realLength - $length)
        . ' characters remain in original article.</em></p>';
    }

}
