<?php

namespace Tickets2\Processors\Web\File;

use MODX\Revolution\Processors\Processor;
use Tickets2\Model\TicketFile;

class Sort extends Processor
{
    public $classKey = TicketFile::class;

    /**
     * @return array|string
     */
    public function process(): array|string
    {
        $rank = $this->getProperty('rank');
        foreach ($rank as $idx => $id) {
            if (!$file = $this->modx->getObject($this->classKey, (int)$id)) {
                return $this->failure($this->modx->lexicon('ticket_err_file_ns'));
            } elseif ($file->createdby != $this->modx->user->id && !$this->modx->user->isMember('Administrator')) {
                return $this->failure($this->modx->lexicon('ticket_err_file_owner'));
            }
            $file->set('rank', (int)$idx);
            $file->save();
        }
        return $this->success();
    }
} 