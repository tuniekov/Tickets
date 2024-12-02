<?php

namespace Tickets2\Model;

use xPDO\Om\xPDOObject;

/**
 * Class TicketVote
 * @package Tickets2\Model
 * @property int $id
 * @property string $class
 * @property int $owner
 * @property int $value
 * @property string $createdon
 * @property int $createdby
 * @property string $ip
 */
class TicketVote extends xPDOObject
{
    /**
     * @param null $cacheFlag
     *
     * @return bool
     */
    public function save($cacheFlag = null): bool
    {
        $new = $this->isNew();
        $class = $this->get('class');
        $save = parent::save($cacheFlag);
        
        if ($new) {
            $type = '';
            $ticket_id = 0;
            if ($class == TicketComment::class) {
                $type = 'vote_comment';
                /** @var TicketComment $comment */
                if ($comment = $this->xpdo->getObject(TicketComment::class, $this->id)) {
                    /** @var TicketThread $thread */
                    if ($thread = $comment->getOne('Thread')) {
                        $ticket_id = $thread->get('resource');
                    }
                }
            } elseif ($class == Ticket::class) {
                $type = 'vote_ticket';
                $ticket_id = $this->id;
            }
            
            if (!empty($type) && !empty($ticket_id)) {
                $multiplier = $this->get('value');
                /** @var TicketAuthor $profile */
                if ($profile = $this->xpdo->getObject(TicketAuthor::class, $this->get('owner'))) {
                    $profile->addAction(
                        $type,
                        $this->id,
                        $ticket_id,
                        $this->get('createdby'),
                        $multiplier
                    );
                }
            }
        }

        return $save;
    }

    /**
     * @param array $ancestors
     *
     * @return bool
     */
    public function remove(array $ancestors = []): bool
    {
        $type = '';
        $class = $this->get('class');
        if ($class == TicketComment::class) {
            $type = 'vote_comment';
        } elseif ($class == Ticket::class) {
            $type = 'vote_ticket';
        }
        
        if (!empty($type)) {
            /** @var TicketAuthor $profile */
            if ($profile = $this->xpdo->getObject(TicketAuthor::class, $this->get('owner'))) {
                $profile->removeAction(
                    $type,
                    $this->id,
                    $this->get('createdby')
                );
            }
        }

        return parent::remove($ancestors);
    }
} 