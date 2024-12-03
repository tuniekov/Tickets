<?php

namespace Tickets2\Processors\Web\File;

use Tickets2\Model\TicketComment;

class UploadComment extends Upload
{
    public $permission = 'comment_file_upload';
    protected string $class = 'TicketComment';

    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        if (!$this->modx->hasPermission($this->permission)) {
            return $this->modx->lexicon('access_denied');
        }

        $tid = (int)$this->getProperty('tid');
        if (!$this->ticket = $this->modx->getObject(TicketComment::class, $tid)) {
            $this->ticket = $this->modx->newObject(TicketComment::class);
            $this->ticket->set('id', 0);
        }

        if ($source = $this->getProperty('source')) {
            /** @var \MODX\Revolution\Sources\modMediaSource $mediaSource */
            $mediaSource = $this->modx->getObject('sources.modMediaSource', (int)$source);
            $mediaSource->set('ctx', $this->modx->context->key);
            if ($mediaSource->initialize()) {
                $this->mediaSource = $mediaSource;
            }
        }

        if (!$this->mediaSource) {
            return $this->modx->lexicon('ticket_err_source_initialize');
        }

        $this->class = $this->getProperty('class', 'TicketComment');

        return true;
    }
} 