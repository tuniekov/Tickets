<?php
namespace Tickets2\Model\mysql;

use xPDO\xPDO;

class TicketAuthor extends \Tickets2\Model\TicketAuthor
{

    public static $metaMap = array (
        'package' => 'Tickets2\\Model',
        'version' => '3.0',
        'table' => 'tickets2_authors',
        'extends' => 'xPDO\\Om\\xPDOObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'id' => NULL,
            'rating' => 0.0,
            'createdon' => NULL,
            'visitedon' => NULL,
            'tickets2' => 0,
            'comments' => 0,
            'views' => 0,
            'votes_tickets2' => 0.0,
            'votes_comments' => 0.0,
            'stars_tickets2' => 0,
            'stars_comments' => 0,
            'votes_tickets2_up' => 0,
            'votes_tickets2_down' => 0,
            'votes_comments_up' => 0,
            'votes_comments_down' => 0,
            'properties' => NULL,
        ),
        'fieldMeta' => 
        array (
            'id' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'attributes' => 'unsigned',
                'null' => false,
                'index' => 'pk',
            ),
            'rating' => 
            array (
                'dbtype' => 'decimal',
                'precision' => '12,2',
                'phptype' => 'float',
                'null' => true,
                'default' => 0.0,
            ),
            'createdon' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
                'null' => true,
            ),
            'visitedon' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
                'null' => true,
            ),
            'tickets2' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'attributes' => 'unsigned',
                'null' => true,
                'default' => 0,
            ),
            'comments' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'attributes' => 'unsigned',
                'null' => true,
                'default' => 0,
            ),
            'views' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'attributes' => 'unsigned',
                'null' => true,
                'default' => 0,
            ),
            'votes_tickets2' => 
            array (
                'dbtype' => 'decimal',
                'precision' => '12,2',
                'phptype' => 'float',
                'null' => true,
                'default' => 0.0,
            ),
            'votes_comments' => 
            array (
                'dbtype' => 'decimal',
                'precision' => '12,2',
                'phptype' => 'float',
                'null' => true,
                'default' => 0.0,
            ),
            'stars_tickets2' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'attributes' => 'unsigned',
                'null' => true,
                'default' => 0,
            ),
            'stars_comments' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'attributes' => 'unsigned',
                'null' => true,
                'default' => 0,
            ),
            'votes_tickets2_up' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'attributes' => 'unsigned',
                'null' => true,
                'default' => 0,
            ),
            'votes_tickets2_down' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'attributes' => 'unsigned',
                'null' => true,
                'default' => 0,
            ),
            'votes_comments_up' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'attributes' => 'unsigned',
                'null' => true,
                'default' => 0,
            ),
            'votes_comments_down' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'attributes' => 'unsigned',
                'null' => true,
                'default' => 0,
            ),
            'properties' => 
            array (
                'dbtype' => 'text',
                'phptype' => 'json',
                'null' => true,
            ),
        ),
        'indexes' => 
        array (
            'rating' => 
            array (
                'alias' => 'rating',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'rating' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'createdon' => 
            array (
                'alias' => 'createdon',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'createdon' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'visitedon' => 
            array (
                'alias' => 'visitedon',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'visitedon' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'tickets2' => 
            array (
                'alias' => 'tickets2',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'tickets2' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'comments' => 
            array (
                'alias' => 'comments',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'comments' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'views' => 
            array (
                'alias' => 'views',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'views' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
        ),
        'composites' => 
        array (
            'Actions' => 
            array (
                'class' => 'Tickets2\\Model\\TicketAuthorAction',
                'local' => 'id',
                'foreign' => 'createdby',
                'cardinality' => 'many',
                'owner' => 'local',
            ),
        ),
        'aggregates' => 
        array (
            'User' => 
            array (
                'class' => 'MODX\\Revolution\\modUser',
                'local' => 'id',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
            'UserProfile' => 
            array (
                'class' => 'MODX\\Revolution\\modUserProfile',
                'local' => 'id',
                'foreign' => 'internalKey',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
        ),
    );

}
