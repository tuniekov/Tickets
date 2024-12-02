<?php

namespace Tickets2\Model;

use xPDO\Om\xPDOObject;

/**
 * Class TicketTotal
 * @package Tickets2\Model
 * @property int $id
 * @property string $class
 * @property int $tickets2
 * @property int $comments
 * @property int $views
 * @property int $stars
 * @property float $rating
 * @property float $rating_plus
 * @property float $rating_minus
 */
class TicketTotal extends xPDOObject
{
    /**
     * @param null $cacheFlag
     *
     * @return bool
     */
    public function save($cacheFlag = null): bool
    {
        if ($this->isNew()) {
            $this->fromArray($this->fetchValues(), '', false, true);
        }

        return parent::save($cacheFlag);
    }

    /**
     * Get values from database
     * 
     * @return array
     */
    public function fetchValues(): array
    {
        $values = [];

        $id = $this->get('id');
        $class = $this->get('class');
        switch ($class) {
            case Ticket::class:
                /** @var Ticket $ticket */
                if ($ticket = $this->xpdo->getObject(Ticket::class, $id)) {
                    $rating = $ticket->getRating();
                    $values = [
                        'comments' => $ticket->getCommentsCount(),
                        'views' => $ticket->getViewsCount(),
                        'stars' => $ticket->getStarsCount(),
                        'rating' => $rating['rating'],
                        'rating_plus' => $rating['rating_plus'],
                        'rating_minus' => $rating['rating_minus'],
                    ];
                }
                break;
            case TicketComment::class:
                if ($comment = $this->xpdo->getObject(TicketComment::class, $id)) {
                    $values = [
                        'stars' => $this->xpdo->getCount(TicketStar::class, [
                            'id' => $id,
                            'class' => TicketComment::class
                        ]),
                        'rating' => $comment->get('rating'),
                    ];
                }
                break;
            case Tickets2Section::class:
                /** @var Tickets2Section $section */
                if ($section = $this->xpdo->getObject(Tickets2Section::class, $id)) {
                    $rating = $section->getRating();
                    $values = [
                        'tickets2' => $section->getTickets2Count(),
                        'comments' => $section->getCommentsCount(),
                        'views' => $section->getViewsCount(),
                        'stars' => $section->getStarsCount(),
                        'rating' => $rating['rating'],
                        'rating_plus' => $rating['rating_plus'],
                        'rating_minus' => $rating['rating_minus'],
                    ];
                }
                break;
        }
        $this->fromArray($values);

        return $values;
    }
} 