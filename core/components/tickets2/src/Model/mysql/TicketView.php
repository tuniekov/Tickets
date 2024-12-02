<?php
namespace Tickets2\Model\mysql;

use xPDO\xPDO;

class TicketView extends \Tickets2\Model\TicketView
{

    public static $metaMap = array (
        'package' => 'Tickets2\\Model',
        'version' => '3.0',
        'table' => 'tickets2_views',
        'extends' => 'xPDO\\Om\\xPDOObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'parent' => 0,
            'uid' => 0,
            'guest_key' => '',
            'timestamp' => NULL,
        ),
        'fieldMeta' => 
        array (
            'parent' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'attributes' => 'unsigned',
                'null' => false,
                'default' => 0,
                'index' => 'pk',
            ),
            'uid' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'attributes' => 'unsigned',
                'null' => false,
                'default' => 0,
                'index' => 'pk',
            ),
            'guest_key' => 
            array (
                'dbtype' => 'char',
                'precision' => '32',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
                'index' => 'pk',
            ),
            'timestamp' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
                'null' => false,
            ),
        ),
        'indexes' => 
        array (
            'PRIMARY' => 
            array (
                'alias' => 'PRIMARY',
                'primary' => true,
                'unique' => true,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'parent' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'uid' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'guest_key' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
        ),
        'aggregates' => 
        array (
            'Ticket' => 
            array (
                'class' => 'Tickets2\\Model\\Ticket',
                'local' => 'parent',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
        ),
    );

}
