<?php

class Zfplanet_Model_Service_LuceneIndexer
{

    protected $_index = null;

    public function __construct(array $config) {
        if (isset($config['search'])) {
            try {
                $this->_index = Zend_Search_Lucene::open($config['search']['indexPath']);
            } catch (Zend_Search_Lucene_Exception $e) {
                $this->_index = Zend_Search_Lucene::create($config['search']['indexPath']);
            }
        }
    }
    
    public function index(Zfplanet_Model_Entry $entry)
    {
        if (is_null($this->_index)) return;
        $doc = new Zend_Search_Lucene_Document;
        $doc->addField(
            Zend_Search_Lucene_Field::UnIndexed(
                'id', $entry->id, 'utf-8'
            )
        );
        $doc->addField(
            Zend_Search_Lucene_Field::UnIndexed(
                'publishedDate', $entry->publishedDate, 'utf-8'
            )
        );
        $doc->addField(
            Zend_Search_Lucene_Field::Keyword(
                'uri', $entry->uri, 'utf-8'
            )
        );
        $doc->addField(
            Zend_Search_Lucene_Field::Text(
                'title', $entry->title, 'utf-8'
            )
        );
        $doc->addField(
            Zend_Search_Lucene_Field::UnStored(
                'content', $entry->content, 'utf-8'
            )
        );
        $this->_index->addDocument($doc);
        $this->_index->commit();
        $this->_index->optimize();
    }
    
    public function indexAll(array $entries)
    {
        if (is_null($this->_index)) return;
        foreach ($entries as $entry) {
            $this->index($entry);
        }
    }

}
