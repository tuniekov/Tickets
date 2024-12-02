<?php

namespace Tickets2\Processors\Mgr\Comment;

use MODX\Revolution\Processors\Model\UpdateProcessor;
use Tickets2\Model\TicketComment;
use Tickets2\Model\TicketThread;

class Unpublish extends UpdateProcessor
{
    public $classKey = TicketComment::class;
    public $languageTopics = ['tickets2:default'];
    public $beforeSaveEvent = 'OnBeforeCommentUnpublish';
    public $afterSaveEvent = 'OnCommentUnpublish';
    public $permission = 'comment_publish';
    protected bool $_sendEmails = false;

    /**
     * @return bool
     */
    public function beforeSet(): bool
    {
        $this->properties = [];
        return true;
    }

    /**
     * @return bool
     */
    public function beforeSave(): bool
    {
        $this->object->set('published', 0);
        return parent::beforeSave();
    }

    /**
     * @return bool
     */
    public function afterSave(): bool
    {
        $this->object->clearTicketCache();
        /** @var TicketThread $thread */
        if ($thread = $this->object->getOne('Thread')) {
            $thread->updateLastComment();
        }

        $this->modx->cacheManager->delete('tickets2/latest.comments');
        $this->modx->cacheManager->delete('tickets2/latest.tickets2');

        if ($this->_sendEmails) {
            $this->sendCommentMails();
        }

        return parent::afterSave();
    }

    /**
     * Send email notifications
     */
    protected function sendCommentMails(): void
    {
        /** @var TicketThread $thread */
        if ($thread = $this->object->getOne('Thread')) {
            /** @var \Tickets2 $Tickets2 */
            if ($Tickets2 = $this->modx->getService('Tickets2')) {
                $Tickets2->config = $thread->get('properties');
                $Tickets2->sendCommentMails($this->object->toArray());
            }
        }
    }

    /**
     * Log manager action
     */
    public function logManagerAction(): void
    {
        $this->modx->logManagerAction($this->objectType . '_unpublish', $this->classKey,
            $this->object->get($this->primaryKeyField));
    }
} 