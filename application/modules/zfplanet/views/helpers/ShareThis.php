<?php

class Zfplanet_View_Helper_ShareThis extends Zend_View_Helper_Abstract
{

    protected static $_count = 1;

    public function shareThis($publisherId, Zfplanet_Model_Entry $entry)
    {
        if (!preg_match("/^[a-z0-9\-]*$/D", $publisherId)) {
            throw new Exception("Invalid ShareThis published ID");
        }  
        $scriptLink = <<<LINK
http://w.sharethis.com/button/sharethis.js#publisher=$publisherId&amp;type=website&amp;embeds=true&amp;post_services=facebook%2Ctwitter%2Cmyspace%2Cdigg%2Cdelicious%2Cstumbleupon%2Creddit%2Clinkedin%2Cfriendfeed
LINK;
        $this->view->headScript()->appendFile($scriptLink, 'text/javascript');
        $count = self::$_count;
        $script = <<<SCRIPT
<script language="javascript" type="text/javascript">
var object_$count = SHARETHIS.addEntry(
    {title:'{$this->view->escape($entry->title)}',
    url:'{$entry->uri}'},
    {button:false}
);
document.write('<span id="sharethis_$count"><a href="javascript:void(0);">'
    + '<img src="http://w.sharethis.com/images/share-icon-16x16.png" style="vertical-align:middle;padding-right:3px;height:13px;"/>Share This!</a></span>');
var element_$count = document.getElementById("sharethis_$count");
object_$count.attachButton(element_$count);
</script>
SCRIPT;
        self::$_count++;
        return $script;
    }

}
