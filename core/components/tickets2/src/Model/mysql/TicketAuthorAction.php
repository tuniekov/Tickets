<?php
namespace Tickets2\Model\mysql;

use xPDO\xPDO;

class TicketAuthorAction extends \Tickets2\Model\TicketAuthorAction
{

    public static $metaMap = array (
        'package' => 'Tickets2\\Model',
        'version' => '3.0',
        'table' => 'tickets2_author_actions',
        'extends' => 'xPDO\\Om\\xPDOObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'id' => NULL,
            'action' => NULL,
            'owner' => 0,
            'rating' => 0.0,
            'multiplier' => 1,
            'ticket' => 0,
            'section' => 0,
            'createdby' => 0,
            'createdon' => NULL,
            'year' => '0000',
            'month' => 0,
            'day' => 0,
        ),
        'fieldMeta' => 
        array (
            'id' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'null' => false,
                'index' => 'pk',
            ),
            'action' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '50',
                'phptype' => 'string',
                'null' => false,
                'index' => 'pk',
            ),
            'owner' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
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
            'multiplier' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'null' => true,
                'default' => 1,
            ),
            'ticket' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
            'section' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
            ),
            'createdby' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'null' => false,
                'default' => 0,
                'index' => 'pk',
            ),
            'createdon' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
                'null' => false,
            ),
            'year' => 
            array (
                'dbtype' => 'year',
                'precision' => '4',
                'phptype' => 'integer',
                'null' => true,
                'default' => '0000',
            ),
            'month' => 
            array (
                'dbtype' => 'int',
                'precision' => '2',
                'phptype' => 'integer',
                'null' => true,
                'default' => 0,
            ),
            'day' => 
            array (
                'dbtype' => 'int',
                'precision' => '2',
                'phptype' => 'integer',
                'null' => true,
                'default' => 0,
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
                    'id' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'action' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'owner' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'createdby' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                ),
            ),
            'date' => 
            array (
                'alias' => 'date',
                'primary' => false,
                'unique' => false,
                'type' => 'BTREE',
                'columns' => 
                array (
                    'year' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'month' => 
                    array (
                        'length' => '',
                        'collation' => 'A',
                        'null' => false,
                    ),
                    'day' => 
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
            'User' => 
            array (
                'class' => 'MODX\\Revolution\\modUser',
                'local' => 'createdby',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
            'Author' => 
            array (
                'class' => 'Tickets2\\Model\\TicketAuthor',
                'local' => 'createdby',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
            'Ticket' => 
            array (
                'class' => 'Tickets2\\Model\\Ticket',
                'local' => 'ticket',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
            'Section' => 
            array (
                'class' => 'Tickets2\\Model\\Tickets2Section',
                'local' => 'section',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
        ),
    );

}
