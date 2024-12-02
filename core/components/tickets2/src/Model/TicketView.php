<?php

namespace Tickets2\Model;

use xPDO\Om\xPDOObject;

/**
 * Class TicketView
 * @package Tickets2\Model
 * @property int $id
 * @property int $parent
 * @property int $uid
 * @property string $timestamp
 * @property string $guest_key
 */
class TicketView extends xPDOObject
{
    /**
     * @param null $cacheFlag
     *
     * @return bool
     */
    public function save($cacheFlag = null): bool
    {
        $new = $this->isNew();
        $parent = parent::save($cacheFlag);

        if ($new) {
            if ($uid = $this->get('uid')) {
                /** @var TicketAuthor $profile */
                if ($profile = $this->xpdo->getObject(TicketAuthor::class, $uid)) {
                    $profile->addAction(
                        'view',
                        $this->get('parent'),
                        $this->get('parent'),
                        $this->get('uid')
                    );
                }
            } else {
                /** @var Ticket $ticket */
                if ($ticket = $this->xpdo->getObject(Ticket::class, $this->get('parent'))) {
                    /** @var TicketTotal $total */
                    if ($total = $ticket->getOne('Total')) {
                        $total->set('views', $total->get('views') + 1);
                        $total->save();
                    }
                    /** @var Tickets2Section $section */
                    if ($section = $ticket->getOne('Parent')) {
                        if ($total = $section->getOne('Total')) {
                            $total->set('views', $total->get('views') + 1);
                            $total->save();
                        }
                    }
                }
            }
        }

        return $parent;
    }

    /**
     * @param array $ancestors
     *
     * @return bool
     */
    public function remove(array $ancestors = []): bool
    {
        /** @var TicketAuthor $profile */
        if ($profile = $this->xpdo->getObject(TicketAuthor::class, $this->get('uid'))) {
            $profile->removeAction(
                'view',
                $this->get('parent'),
                $this->get('uid')
            );
        }

        return parent::remove($ancestors);
    }
} 