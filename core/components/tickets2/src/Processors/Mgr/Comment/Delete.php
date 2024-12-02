<?php

namespace Tickets2\Processors\Mgr\Comment;

use MODX\Revolution\Processors\Model\UpdateProcessor;
use Tickets2\Model\TicketComment;
use Tickets2\Model\TicketThread;

class Delete extends UpdateProcessor
{
    public $classKey = TicketComment::class;
    public $languageTopics = ['tickets2:default'];
    public $beforeSaveEvent = 'OnBeforeCommentDelete';
    public $afterSaveEvent = 'OnCommentDelete';
    public $permission = 'comment_delete';

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
        $this->object->fromArray([
            'deleted' => 1,
            'deletedon' => time(),
            'deletedby' => $this->modx->user->get('id'),
        ]);

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

        return parent::afterSave();
    }

    /**
     * Log manager action
     */
    public function logManagerAction(): void
    {
        $this->modx->logManagerAction($this->objectType . '_delete', $this->classKey,
            $this->object->get($this->primaryKeyField));
    }
} 