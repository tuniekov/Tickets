<?php
$xpdo_meta_map = array (
    'version' => '3.0',
    'namespace' => 'Tickets2\\Model',
    'namespacePrefix' => 'Tickets2',
    'class_map' => 
    array (
        'MODX\\Revolution\\modResource' => 
        array (
            0 => 'Tickets2\\Model\\Tickets2Section',
            1 => 'Tickets2\\Model\\Ticket',
        ),
        'xPDO\\Om\\xPDOSimpleObject' => 
        array (
            0 => 'Tickets2\\Model\\TicketComment',
            1 => 'Tickets2\\Model\\TicketThread',
            2 => 'Tickets2\\Model\\TicketFile',
        ),
        'xPDO\\Om\\xPDOObject' => 
        array (
            0 => 'Tickets2\\Model\\TicketVote',
            1 => 'Tickets2\\Model\\TicketStar',
            2 => 'Tickets2\\Model\\TicketAuthor',
            3 => 'Tickets2\\Model\\TicketAuthorAction',
            4 => 'Tickets2\\Model\\TicketTotal',
            5 => 'Tickets2\\Model\\TicketView',
        ),
    ),
);