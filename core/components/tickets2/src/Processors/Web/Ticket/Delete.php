<?php

namespace Tickets2\Processors\Web\Ticket;

use MODX\Revolution\modResource;
use MODX\Revolution\Processors\Resource\Delete as DeleteResource;
use Tickets2\Model\Ticket;

class Delete extends DeleteResource
{
    public $classKey = Ticket::class;
    public $permission = 'ticket_delete';

    /**
     * @return bool
     */
    public function checkPermissions()
    {
        $id = $this->getProperty('id', false);
        $this->resource = $this->modx->getObject(modResource::class, $id);
        if (empty($this->resource)) {
            return $this->modx->lexicon('resource_err_nfs', ['id' => $id]);
        }
        /* resource owner is this user? */
        if ($this->resource->get('createdby') != $this->modx->user->id) {
            return false;
        }
        return true;
    }
} 