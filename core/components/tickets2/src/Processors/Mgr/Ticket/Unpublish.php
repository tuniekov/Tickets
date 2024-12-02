<?php

namespace Tickets2\Processors\Mgr\Ticket;

use MODX\Revolution\Processors\Resource\Unpublish as ResourceUnpublish;
use Tickets2\Model\Ticket;

class Unpublish extends ResourceUnpublish
{
    public $classKey = Ticket::class;
    public $permission = 'ticket_publish';
} 