<?php

namespace Tickets2\Processors\Mgr\Ticket;

use MODX\Revolution\Processors\Resource\Delete as ResourceDelete;
use Tickets2\Model\Ticket;

class Delete extends ResourceDelete
{
    public $classKey = Ticket::class;
    public $permission = 'ticket_delete';
} 