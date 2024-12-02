<?php

namespace Tickets2\Processors\Mgr\Thread;

use MODX\Revolution\Processors\Model\UpdateProcessor;
use Tickets2\Model\TicketThread;

class Open extends UpdateProcessor
{
    public $classKey = TicketThread::class;
    public $objectType = 'TicketThread';
    public $languageTopics = ['tickets2:default'];
    public $beforeSaveEvent = 'OnBeforeTicketThreadOpen';
    public $afterSaveEvent = 'OnTicketThreadOpen';
    public $permission = 'thread_close';

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
            'closed' => 0,
        ]);

        return parent::beforeSave();
    }

    /**
     * @return bool
     */
    public function afterSave(): bool
    {
        $this->modx->cacheManager->delete('tickets2/latest.comments');
        $this->modx->cacheManager->delete('tickets2/latest.tickets2');

        return true;
    }

    /**
     * Log manager action
     */
    public function logManagerAction(): void
    {
        $this->modx->logManagerAction($this->objectType . '_open', $this->classKey,
            $this->object->get($this->primaryKeyField));
    }
} 