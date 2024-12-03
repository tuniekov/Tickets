<?php

namespace Tickets2\Processors\Mgr\Comment;

use MODX\Revolution\Processors\Model\UpdateProcessor;
use Tickets2\Model\TicketComment;
use Tickets2\Model\TicketThread;

class Undelete extends UpdateProcessor
{
    public $classKey = TicketComment::class;
    public $languageTopics = ['tickets2:default'];
    public $beforeSaveEvent = 'OnBeforeCommentUndelete';
    public $afterSaveEvent = 'OnCommentUndelete';
    public $permission = 'comment_delete';

    /**
     * @return bool
     */
    public function beforeSet()
    {
        $this->properties = [];
        return true;
    }

    /**
     * @return bool
     */
    public function beforeSave()
    {
        $this->object->fromArray([
            'deleted' => 0,
            'deletedon' => null,
            'deletedby' => 0,
        ]);

        return parent::beforeSave();
    }

    /**
     * @return bool
     */
    public function afterSave()
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
    public function logManagerAction()
    {
        $this->modx->logManagerAction($this->objectType . '_undelete', $this->classKey,
            $this->object->get($this->primaryKeyField));
    }
} 