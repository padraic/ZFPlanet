<?php

class Zfplanet_View_Helper_ListBloggers extends Zend_View_Helper_Abstract
{

    public function listBloggers($count = 50)
    {
        $bloggers = $this->_getBloggers($count);
        if (count($bloggers) == 0) {
            return '<p>None found</p>';
        }
        $out = '<ul class="list-bloggers">';
        foreach($bloggers as $blogger) {
            $out .= '<li><a href="'
                . $this->view->escape($blogger->Feed->Blog->uri)
                . '">'
                . $this->view->escape($blogger->author)
                . '</a></li>';
        }
        $out .= '</ul>';
        return $out;
    }
    
    protected function _getBloggers($count)
    {
        $bloggers = Doctrine_Query::create()
            ->select('DISTINCT author as author, feedId')
            ->from('Zfplanet_Model_Entry')
            ->groupBy('author')
            ->orderBy('publishedDate DESC')
            ->limit($count)
            ->execute();
        return $bloggers;
    }

}
