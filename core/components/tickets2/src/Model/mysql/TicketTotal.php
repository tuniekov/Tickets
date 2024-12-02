<?php
namespace Tickets2\Model\mysql;

use xPDO\xPDO;

class TicketTotal extends \Tickets2\Model\TicketTotal
{

    public static $metaMap = array (
        'package' => 'Tickets2\\Model',
        'version' => '3.0',
        'table' => 'tickets2_totals',
        'extends' => 'xPDO\\Om\\xPDOObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'id' => NULL,
            'class' => NULL,
            'tickets2' => 0,
            'comments' => 0,
            'views' => 0,
            'stars' => 0,
            'rating' => 0,
            'rating_plus' => 0,
            'rating_minus' => 0,
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
            'class' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '100',
                'phptype' => 'string',
                'null' => false,
                'index' => 'pk',
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
                'null' => true,
                'attributes' => 'unsigned',
                'default' => 0,
            ),
            'stars' => 
            array (
                'dbtype' => 'int',
                'precision' => '10',
                'phptype' => 'integer',
                'null' => true,
                'attributes' => 'unsigned',
                'default' => 0,
            ),
            'rating' => 
            array (
                'dbtype' => 'smallint',
                'precision' => '5',
                'phptype' => 'integer',
                'null' => true,
                'default' => 0,
            ),
            'rating_plus' => 
            array (
                'dbtype' => 'smallint',
                'precision' => '5',
                'phptype' => 'integer',
                'null' => true,
                'attributes' => 'unsigned',
                'default' => 0,
            ),
            'rating_minus' => 
            array (
                'dbtype' => 'smallint',
                'precision' => '5',
                'phptype' => 'integer',
                'null' => true,
                'attributes' => 'unsigned',
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
                    'class' => 
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
            'Tickets2Section' => 
            array (
                'class' => 'Tickets2\\Model\\Ticket',
                'local' => 'id',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
            'Ticket' => 
            array (
                'class' => 'Tickets2\\Model\\Ticket',
                'local' => 'id',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
            'TicketComment' => 
            array (
                'class' => 'Tickets2\\Model\\Ticket',
                'local' => 'id',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
            'TicketThread' => 
            array (
                'class' => 'Tickets2\\Model\\Ticket',
                'local' => 'id',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
        ),
    );

}
