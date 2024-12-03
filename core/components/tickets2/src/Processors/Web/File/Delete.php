<?php

namespace Tickets2\Processors\Web\File;

use MODX\Revolution\Processors\Processor;
use Tickets2\Model\TicketFile;

class Delete extends Processor
{
    public $classKey = TicketFile::class;
    public $permission = 'ticket_file_upload';

    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        if (!$this->modx->hasPermission($this->permission)) {
            return $this->modx->lexicon('access_denied');
        }

        return true;
    }

    /**
     * @return array|string
     */
    public function process()
    {
        $id = (int)$this->getProperty('id');
        /** @var TicketFile $file */
        if (!$file = $this->modx->getObject($this->classKey, $id)) {
            return $this->failure($this->modx->lexicon('ticket_err_file_ns'));
        } elseif ($file->createdby != $this->modx->user->id && !$this->modx->user->isMember('Administrator')) {
            return $this->failure($this->modx->lexicon('ticket_err_file_owner'));
        }

        if ($file->get('deleted')) {
            $file->set('deleted', 0);
        } else {
            $file->set('deleted', 1);
        }
        $file->save();

        return $this->success();
    }
} 