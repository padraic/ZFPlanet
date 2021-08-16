<?php

/**
 * Zfplanet_Model_Base_Blog
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $feedId
 * @property string $contactName
 * @property string $contactEmail
 * @property string $uri
 * @property Doctrine_Collection $Feeds
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class Zfplanet_Model_Base_Blog extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('blog');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('feedId', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('contactName', 'string', 100, array(
             'type' => 'string',
             'length' => '100',
             ));
        $this->hasColumn('contactEmail', 'string', 100, array(
             'type' => 'string',
             'length' => '100',
             ));
        $this->hasColumn('uri', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('Zfplanet_Model_Feed as Feeds', array(
             'local' => 'id',
             'foreign' => 'blogId'));

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