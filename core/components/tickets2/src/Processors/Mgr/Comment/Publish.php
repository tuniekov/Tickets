<?php

namespace Tickets2\Processors\Mgr\Comment;

use MODX\Revolution\Processors\Model\UpdateProcessor;
use Tickets2\Model\TicketComment;
use Tickets2\Model\TicketThread;

/**
 * Publish a comment
 *
 * @package tickets2
 * @subpackage processors
 */
class Publish extends UpdateProcessor
{
    // public $objectType = 'TicketComment';
    public $classKey = TicketComment::class;
    public $languageTopics = ['tickets2:default'];
    public $beforeSaveEvent = 'OnBeforeCommentPublish';
    public $afterSaveEvent = 'OnCommentPublish';
    public $permission = 'comment_publish';
    protected $_sendEmails = false;

    /**
     * Clear properties before set
     *
     * @return bool
     */
    public function beforeSet()
    {
        $this->properties = [];
        return true;
    }

    /**
     * Set comment as published and check if we need to send emails
     *
     * @return bool
     */
    public function beforeSave()
    {
        $this->object->set('published', 1);
        $properties = $this->object->get('properties');
        if (array_key_exists('was_published', $properties)) {
            unset($properties['was_published']);
            $this->object->set('properties', $properties);
            $this->_sendEmails = true;
        }

        return parent::beforeSave();
    }

    /**
     * Clear cache and send emails after save
     *
     * @return bool
     */
    public function afterSave()
    {
        $this->object->clearTicketCache();
        
        if ($thread = $this->object->getOne('Thread')) {
            $thread->updateLastComment();
        }

        $this->modx->cacheManager->delete('tickets/latest.comments');
        $this->modx->cacheManager->delete('tickets/latest.tickets');

        if ($this->_sendEmails) {
            $this->sendCommentMails();
        }

        return parent::afterSave();
    }

    /**
     * Send notification emails about new comment
     *
     * @return void
     */
    protected function sendCommentMails()
    {
        if ($thread = $this->object->getOne('Thread')) {
            if ($Tickets = $this->modx->getService('Tickets2')) {
                $Tickets->config = $thread->get('properties');
                $Tickets->sendCommentMails($this->object->toArray());
            }
        }
    }

    /**
     * Log manager action
     *
     * @param string $action The action to log
     * @return void
     */
    public function logManagerAction($action = '')
    {
        $this->modx->logManagerAction(
            $this->objectType . '_publish',
            $this->classKey,
            $this->object->get($this->primaryKeyField)
        );
    }
}
