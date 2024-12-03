<?php

namespace Tickets2\Processors\Mgr\Comment;

use MODX\Revolution\Processors\Model\UpdateProcessor;
use Tickets2\Model\TicketComment;
use Tickets2\Model\TicketThread;

class Update extends UpdateProcessor
{
    public $classKey = TicketComment::class;
    public $languageTopics = ['tickets2:default'];
    public $beforeSaveEvent = 'OnBeforeCommentSave';
    public $afterSaveEvent = 'OnCommentSave';
    public $permission = 'comment_save';
    protected int $old_thread = 0;

    /**
     * @return bool|null|string
     */
    public function beforeSet()
    {
        if (!$this->getProperty('name')) {
            $this->unsetProperty('name');
        }
        if (!$this->getProperty('email')) {
            $this->unsetProperty('email');
        }
        if (!trim($this->getProperty('text'))) {
            return $this->modx->lexicon('ticket_err_empty');
        }

        $this->old_thread = $this->object->get('thread');
        $parent = $this->getProperty('parent');
        // New parent is in other thread
        if ($parent != 0 && $parent != $this->object->get('parent')) {
            if ($parent = $this->modx->getObject(TicketComment::class, ['id' => (int)$parent])) {
                $this->setProperty('thread', $parent->get('thread'));
            }
        }
        $this->unsetProperty('action');

        return parent::beforeSet();
    }

    /**
     * @return bool
     */
    public function beforeSave()
    {
        $text = $this->getProperty('text');

        /** @var \Tickets2 $Tickets2 */
        if ($Tickets2 = $this->modx->getService('Tickets2')) {
            $this->object->fromArray([
                'editedon' => time(),
                'editedby' => $this->modx->user->id,
                'text' => $Tickets2->Jevix($text, 'Comment'),
                'raw' => $text,
            ]);
        }

        return parent::beforeSave();
    }

    /**
     * @return bool
     */
    public function afterSave()
    {
        $new_thread = $this->object->get('thread');
        if ($this->old_thread != $new_thread) {
            $this->object->changeThread($this->old_thread, $new_thread);
        } else {
            $this->object->clearTicketCache();
        }

        return parent::afterSave();
    }
} 