<?php
 
class ZFExt_View_Helper_IncludeModifiedDate extends Zend_View_Helper_Abstract
{
 
    public function includeModifiedDate($uri) {
        $parts = parse_url($uri);
        $root = getcwd();
        $mtime = filemtime($root . $parts['path']);
        return preg_replace(
            "/(\.[a-z0-9]+)(\?*.*)$/",
            '.'.$mtime.'$1$2',
            $uri
        );
    }
 
}
