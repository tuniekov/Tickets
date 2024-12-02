<?php

namespace Tickets2\Processors\Mgr\Comment;

use MODX\Revolution\Processors\Model\CreateProcessor;
use Tickets2\Model\TicketComment;
use Tickets2\Model\TicketThread;

class Create extends CreateProcessor
{
    public $classKey = TicketComment::class;
    public $languageTopics = ['tickets2:default'];
    public $permission = 'comment_save';
    public $beforeSaveEvent = 'OnBeforeCommentSave';
    public $afterSaveEvent = 'OnCommentSave';
    /** @var TicketThread $thread */
    private ?TicketThread $thread;

    /**
     * @return bool|string
     */
    public function initialize(): bool|string
    {
        $this->thread = $this->modx->getObject(TicketThread::class, (int)$this->getProperty('thread'));
        if (!$this->thread) {
            return $this->modx->lexicon('ticket_err_wrong_thread');
        } elseif ($this->thread->closed) {
            return $this->modx->lexicon('ticket_thread_err_closed');
        } elseif ($this->thread->deleted) {
            return $this->modx->lexicon('ticket_thread_err_deleted');
        }

        return parent::initialize();
    }

    /**
     * @return bool|null|string
     */
    public function beforeSet(): bool|string|null
    {
        if (!trim($this->getProperty('text'))) {
            return $this->modx->lexicon('ticket_err_empty');
        }

        // Comment values
        $ip = $this->modx->request->getClientIp();
        $this->setProperties([
            'parent' => (int)$this->getProperty('parent'),
            'thread' => $this->thread->id,
            'ip' => $ip['ip'],
            'email' => $this->modx->user->Profile->email,
            'name' => $this->modx->user->Profile->fullname,
            'createdon' => date('Y-m-d H:i:s'),
            'createdby' => $this->modx->user->id,
            'published' => true,
        ]);
        $this->unsetProperty('action');

        return parent::beforeSet();
    }

    /**
     * @return bool
     */
    public function beforeSave(): bool
    {
        $text = $this->getProperty('text');

        /** @var \Tickets2 $Tickets2 */
        if ($Tickets2 = $this->modx->getService('Tickets2')) {
            $this->object->fromArray([
                'text' => $Tickets2->Jevix($text, 'Comment'),
                'raw' => $text,
            ]);
        }

        return parent::beforeSave();
    }

    /**
     * @return bool
     */
    public function afterSave(): bool
    {
        $this->thread->fromArray([
            'comment_last' => $this->object->get('id'),
            'comment_time' => $this->object->get('createdon'),
        ]);
        $this->thread->save();

        $this->thread->updateCommentsCount();
        $this->object->clearTicketCache();

        return parent::afterSave();
    }
} 