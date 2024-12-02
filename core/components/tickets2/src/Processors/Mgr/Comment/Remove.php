<?php

namespace Tickets2\Processors\Mgr\Comment;

use MODX\Revolution\Processors\Model\RemoveProcessor;
use Tickets2\Model\TicketComment;
use Tickets2\Model\TicketThread;

class Remove extends RemoveProcessor
{
    public $checkRemovePermission = true;
    public $objectType = 'TicketComment';
    public $classKey = TicketComment::class;
    public $languageTopics = ['tickets2'];
    public $beforeRemoveEvent = 'OnBeforeCommentRemove';
    public $afterRemoveEvent = 'OnCommentRemove';
    public $permission = 'comment_remove';
    private array $children = [];

    /**
     * @return bool|null|string
     */
    public function initialize(): bool|string|null
    {
        $parent = parent::initialize();
        if ($this->checkRemovePermission && !$this->modx->hasPermission($this->permission)) {
            return $this->modx->lexicon('access_denied');
        }

        return $parent;
    }

    /**
     * @return bool
     */
    public function beforeRemove(): bool
    {
        $this->getChildren($this->object);
        $children = $this->modx->getIterator(TicketComment::class, ['id:IN' => $this->children]);
        /** @var TicketComment $child */
        foreach ($children as $child) {
            $child->remove();
        }

        return true;
    }

    /**
     * @param TicketComment $parent
     */
    protected function getChildren(TicketComment $parent): void
    {
        $children = $parent->getMany('Children');
        if (count($children) > 0) {
            /** @var TicketComment $child */
            foreach ($children as $child) {
                $this->children[] = $child->get('id');
                $this->getChildren($child);
            }
        }
    }

    /**
     * @return bool
     */
    public function afterRemove(): bool
    {
        $this->object->clearTicketCache();
        /** @var TicketThread $thread */
        if ($thread = $this->object->getOne('Thread')) {
            $thread->updateLastComment();
        }

        $this->modx->cacheManager->delete('tickets2/latest.comments');
        $this->modx->cacheManager->delete('tickets2/latest.tickets2');

        return parent::afterRemove();
    }

    /**
     * Log manager action
     */
    public function logManagerAction(): void
    {
        $this->modx->logManagerAction($this->objectType . '_remove', $this->classKey,
            $this->object->get($this->primaryKeyField));
    }
} 