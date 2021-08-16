<?php

/**
 * Zfplanet_Model_Base_Feed
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $id
 * @property integer $blogId
 * @property string $title
 * @property string $uri
 * @property string $type
 * @property integer $isActive
 * @property timestamp $lastSynchronised
 * @property Zfplanet_Model_Blog $Blog
 * @property Zfplanet_Model_FeedMeta $FeedMeta
 * @property Doctrine_Collection $Entries
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class Zfplanet_Model_Base_Feed extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('feed');
        $this->hasColumn('id', 'string', 255, array(
             'type' => 'string',
             'primary' => true,
             'length' => '255',
             ));
        $this->hasColumn('blogId', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('title', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('uri', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('type', 'string', 8, array(
             'type' => 'string',
             'length' => '8',
             ));
        $this->hasColumn('isActive', 'integer', 1, array(
             'type' => 'integer',
             'length' => '1',
             ));
        $this->hasColumn('lastSynchronised', 'timestamp', null, array(
             'type' => 'timestamp',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Zfplanet_Model_Blog as Blog', array(
             'local' => 'blogId',
             'foreign' => 'id',
             'onDelete' => 'CASCADE'));

        $this->hasOne('Zfplanet_Model_FeedMeta as FeedMeta', array(
             'local' => 'id',
             'foreign' => 'feedId'));

        $this->hasMany('Zfplanet_Model_Entry as Entries', array(
             'local' => 'id',
             'foreign' => 'feedId'));

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