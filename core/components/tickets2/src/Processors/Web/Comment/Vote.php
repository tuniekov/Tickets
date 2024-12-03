<?php

namespace Tickets2\Processors\Web\Comment;

use MODX\Revolution\Processors\Model\CreateProcessor;
use Tickets2\Model\Ticket;
use Tickets2\Model\TicketComment;
use Tickets2\Model\TicketThread;
use Tickets2\Model\TicketVote;
use Tickets2\Model\Tickets2Section;

class Vote extends CreateProcessor
{
    public $classKey = TicketVote::class;
    public $languageTopics = ['tickets2:default'];
    public $beforeSaveEvent = 'OnBeforeCommentVote';
    public $afterSaveEvent = 'OnCommentVote';
    public $permission = 'comment_vote';
    /** @var TicketComment $comment */
    private ?TicketComment $comment;

    /**
     * @return bool|null|string
     */
    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');

        if (!$this->modx->user->isAuthenticated($this->modx->context->key)) {
            return $this->modx->lexicon('permission_denied');
        } elseif (!$this->comment = $this->modx->getObject(TicketComment::class, compact('id'))) {
            return $this->modx->lexicon('ticket_comment_err_comment');
        } elseif ($this->comment->createdby == $this->modx->user->id) {
            return $this->modx->lexicon('ticket_comment_err_vote_own');
        } elseif ($this->modx->getCount($this->classKey,
            ['id' => $id, 'createdby' => $this->modx->user->get('id'), 'class' => 'TicketComment'])
        ) {
            return $this->modx->lexicon('ticket_comment_err_vote_already');
        }

        return true;
    }

    /**
     * @return bool
     */
    public function beforeSave()
    {
        /** @var TicketThread $thread */
        if ($thread = $this->comment->getOne('Thread')) {
            /** @var Ticket $ticket */
            if ($ticket = $thread->getOne('Ticket')) {
                /** @var Tickets2Section $section */
                if ($section = $ticket->getOne('Section')) {
                    $ratings = $section->getProperties('ratings');
                    if (isset($ratings['days_comment_vote']) && $ratings['days_comment_vote'] !== '') {
                        $max = strtotime($this->comment->get('createdon')) + ((float)$ratings['days_comment_vote'] * 86400);
                        if (time() > $max) {
                            return $this->modx->lexicon('ticket_err_vote_comment_days');
                        }
                    }
                }
            }
        }

        $this->modx->getRequest();
        $ip = $this->modx->request->getClientIp();

        $value = $this->getProperty('value');
        $value = $value > 0
            ? 1
            : -1;

        $this->object->set('id', $this->comment->get('id'));
        $this->object->set('owner', $this->comment->get('createdby'));
        $this->object->set('class', 'TicketComment');
        $this->object->set('value', $value);
        $this->object->set('ip', $ip['ip']);
        $this->object->set('createdon', date('Y-m-d H:i:s'));
        $this->object->set('createdby', $this->modx->user->get('id'));

        return true;
    }

    /**
     * @return array|string
     */
    public function cleanup()
    {
        $rating = $this->comment->updateRating();

        return $this->success('', $rating);
    }
} 