<?php

namespace Tickets2\Processors\Mgr\Thread;

use MODX\Revolution\Processors\Model\UpdateProcessor;
use Tickets2\Model\TicketThread;

class Undelete extends UpdateProcessor
{
    public $classKey = TicketThread::class;
    public $objectType = 'TicketThread';
    public $languageTopics = ['tickets2:default'];
    public $beforeSaveEvent = 'OnBeforeTicketThreadUndelete';
    public $afterSaveEvent = 'OnTicketThreadUndelete';
    public $permission = 'thread_delete';

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
        $this->modx->cacheManager->delete('tickets2/latest.comments');
        $this->modx->cacheManager->delete('tickets2/latest.tickets2');

        return true;
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