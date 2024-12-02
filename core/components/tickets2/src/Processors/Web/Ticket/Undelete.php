<?php

namespace Tickets2\Processors\Web\Ticket;

// use MODX\Revolution\Processors\Resource\Undelete;
use MODX\Revolution\modResource;
use Tickets2\Model\Ticket;

class Undelete extends MODX\Revolution\Processors\Resource\Undelete
{
    public $classKey = Ticket::class;
    public $permission = 'ticket_delete';

    /**
     * @return bool
     */
    public function checkPermissions(): bool
    {
        $id = $this->getProperty('id', false);
        $this->resource = $this->modx->getObject(modResource::class, $id);
        if (empty($this->resource)) {
            return false;
        }
        /* resource was deleted by this user? */
        if ($this->resource->get('deletedby') != $this->modx->user->id) {
            return false;
        }
        return true;
    }
} 