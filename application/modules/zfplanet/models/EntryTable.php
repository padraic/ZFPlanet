<?php


class Zfplanet_Model_EntryTable extends Doctrine_Table
    implements Zend_Paginator_Adapter_Interface
{

    /**
     * Returns an collection of items for a page.
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $entries = Doctrine_Query::create()
            ->from('Zfplanet_Model_Entry')
            ->orderBy('publishedDate DESC')
            ->limit($itemCountPerPage)
            ->offset($offset)
            ->execute();
        return $entries;
    }
    
    /**
     * Returns the total number of rows in the collection.
     *
     * @return integer
     */
    public function count()
    {
        $count = Doctrine_Query::create()
            ->select('COUNT(id) as total')
            ->from('Zfplanet_Model_Entry')
            ->fetchone();
        return $count->total;
    }

}
