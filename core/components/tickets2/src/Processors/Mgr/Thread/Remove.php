<?php

namespace Tickets2\Processors\Mgr\Thread;

use MODX\Revolution\Processors\Model\RemoveProcessor;
use Tickets2\Model\TicketComment;
use Tickets2\Model\TicketThread;

class Remove extends RemoveProcessor
{
    public $checkRemovePermission = true;
    public $classKey = TicketThread::class;
    public $objectType = 'TicketThread';
    public $languageTopics = ['tickets2'];
    public $beforeRemoveEvent = 'OnBeforeTicketThreadRemove';
    public $afterRemoveEvent = 'OnTicketThreadRemove';
    public $permission = 'thread_remove';

    /**
     * @return bool
     */
    public function beforeRemove()
    {
        $comments = $this->modx->getIterator(TicketComment::class, ['thread' => $this->object->get('id')]);
        /** @var TicketComment $comment */
        foreach ($comments as $comment) {
            $comment->remove();
        }

        return true;
    }

    /**
     * Log manager action
     */
    public function logManagerAction()
    {
        $this->modx->logManagerAction($this->objectType . '_remove', $this->classKey,
            $this->object->get($this->primaryKeyField));
    }

    /**
     * @return bool
     */
    public function afterRemove()
    {
        $this->modx->cacheManager->delete('tickets2/latest.comments');
        $this->modx->cacheManager->delete('tickets2/latest.tickets2');

        return true;
    }
} 