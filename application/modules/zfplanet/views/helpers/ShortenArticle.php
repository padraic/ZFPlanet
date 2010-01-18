<?php

class Zfplanet_View_Helper_ShortenArticle extends Zend_View_Helper_Abstract
{

    public function shortenArticle($content, $length = 5000, $encoding = null)
    {
        
        if (is_null($encoding)) $encoding = $this->view->getEncoding();
        $realLength = iconv_strlen($content, $encoding);
        if ($realLength <= $length) return $content;
        $content = iconv_substr($content, 0, $length, $encoding);
        if (class_exists('tidy', false)) {
            $tidy = new tidy;
            $tidy->parseString(
                $this->_addContinuationMarker($content),
                array(
                    'output-xhtml' => true,
                    'show-body-only' => true
                ),
                str_replace('-','',$encoding)
            );
            $tidy->cleanRepair();
            $content = (string) $tidy;
        } else {
            $content = $this->_addContinuationMarker($content);
        }
        return $content . '<p style="margin-bottom:0;">'
        . '<br/><em>Content was truncated. Another ' . ($realLength - $length)
        . ' characters remain in original article.</em></p>';
    }
    
    /**
     * Add a generic indicator that article is truncated without accidentally
     * appending the indicator to URIs in links and images, instead broken links
     * and images are stripped entirely before its appended (also cleaning up
     * the source).
     *
     * @param string $content
     * @return string
     */
    protected function _addContinuationMarker($content)
    {
        $content = preg_replace("/(<a[^>]*)$/D", '', $content);
        $content = preg_replace("/(<img[^>]*)$/D", '', $content);
        return $content . '<span><em>[...]</em></span>';
    }

}
