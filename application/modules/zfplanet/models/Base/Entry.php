<?php

/**
 * Zfplanet_Model_Base_Entry
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $id
 * @property string $feedId
 * @property string $title
 * @property string $uri
 * @property clob $content
 * @property string $description
 * @property timestamp $publishedDate
 * @property timestamp $updatedDate
 * @property string $author
 * @property string $signatureHash
 * @property integer $isActive
 * @property Zfplanet_Model_Feed $Feed
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class Zfplanet_Model_Base_Entry extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('entry');
        $this->hasColumn('id', 'string', 255, array(
             'type' => 'string',
             'primary' => true,
             'length' => '255',
             ));
        $this->hasColumn('feedId', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('title', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('uri', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('content', 'clob', null, array(
             'type' => 'clob',
             ));
        $this->hasColumn('description', 'string', 1000, array(
             'type' => 'string',
             'length' => '1000',
             ));
        $this->hasColumn('publishedDate', 'timestamp', null, array(
             'type' => 'timestamp',
             ));
        $this->hasColumn('updatedDate', 'timestamp', null, array(
             'type' => 'timestamp',
             ));
        $this->hasColumn('author', 'string', 100, array(
             'type' => 'string',
             'length' => '100',
             ));
        $this->hasColumn('signatureHash', 'string', 32, array(
             'type' => 'string',
             'length' => '32',
             ));
        $this->hasColumn('isActive', 'integer', 1, array(
             'type' => 'integer',
             'length' => '1',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Zfplanet_Model_Feed as Feed', array(
             'local' => 'feedId',
             'foreign' => 'id',
             'onDelete' => 'CASCADE'));

        $timestampable0 = new Doctrine_Template_Timestampable(array(
             'created' => 
             array(
              'name' => 'dateCreated',
             ),
             'updated' => 
             array(
              'name' => 'dateModified',
             ),
             ));
        $this->actAs($timestampable0);
    }
}