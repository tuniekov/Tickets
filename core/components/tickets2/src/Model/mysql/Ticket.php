<?php
namespace Tickets2\Model\mysql;

use xPDO\xPDO;

class Ticket extends \Tickets2\Model\Ticket
{

    public static $metaMap = array (
        'package' => 'Tickets2\\Model',
        'version' => '3.0',
        'extends' => 'MODX\\Revolution\\modResource',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'class_key' => 'Tickets2\\\\Model\\\\Ticket',
        ),
        'fieldMeta' => 
        array (
            'class_key' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '100',
                'phptype' => 'string',
                'null' => false,
                'default' => 'Tickets2\\\\Model\\\\Ticket',
            ),
        ),
        'composites' => 
        array (
            'Views' => 
            array (
                'class' => 'Tickets2\\Model\\TicketView',
                'local' => 'id',
                'foreign' => 'parent',
                'cardinality' => 'many',
                'owner' => 'local',
            ),
            'Votes' => 
            array (
                'class' => 'Tickets2\\Model\\TicketVote',
                'local' => 'id',
                'foreign' => 'id',
                'cardinality' => 'many',
                'owner' => 'local',
            ),
            'Stars' => 
            array (
                'class' => 'Tickets2\\Model\\TicketStar',
                'local' => 'id',
                'foreign' => 'id',
                'cardinality' => 'many',
                'owner' => 'local',
            ),
            'Files' => 
            array (
                'class' => 'Tickets2\\Model\\TicketFile',
                'local' => 'id',
                'foreign' => 'parent',
                'cardinality' => 'many',
                'owner' => 'local',
            ),
            'Total' => 
            array (
                'class' => 'Tickets2\\Model\\TicketTotal',
                'local' => 'id',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'local',
            ),
        ),
        'aggregates' => 
        array (
            'Section' => 
            array (
                'class' => 'Tickets2\\Model\\Tickets2Section',
                'local' => 'parent',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
            'Threads' => 
            array (
                'class' => 'Tickets2\\Model\\TicketThread',
                'local' => 'id',
                'foreign' => 'resource',
                'cardinality' => 'many',
                'owner' => 'local',
            ),
        ),
    );

}
