<?php
namespace Tickets2\Model\mysql;

use xPDO\xPDO;

class Tickets2Section extends \Tickets2\Model\Tickets2Section
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
            'class_key' => 'Tickets2\\\\Model\\\\Tickets2Section',
        ),
        'fieldMeta' => 
        array (
            'class_key' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '100',
                'phptype' => 'string',
                'null' => false,
                'default' => 'Tickets2\\\\Model\\\\Tickets2Section',
            ),
        ),
        'composites' => 
        array (
            'Tickets2' => 
            array (
                'class' => 'Tickets2\\Model\\Ticket',
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
    );

}
