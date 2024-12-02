<?php

namespace Tickets2\Processors\Mgr\Ticket;

use MODX\Revolution\Processors\Resource\Undelete as ResourceUndelete;
use Tickets2\Model\Ticket;

class Undelete extends ResourceUndelete
{
    public $classKey = Ticket::class;
    public $permission = 'ticket_delete';
} 