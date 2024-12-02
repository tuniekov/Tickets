<?php

namespace Tickets2\Model;

use xPDO\Om\xPDOObject;

/**
 * Class TicketAuthorAction
 * @package Tickets2\Model
 * @property int $id
 * @property string $action
 * @property int $owner
 * @property float $rating
 * @property int $multiplier
 * @property int $ticket
 * @property int $section
 * @property int $createdby
 * @property string $createdon
 * @property int $year
 * @property int $month
 * @property int $day
 */
class TicketAuthorAction extends xPDOObject
{
    /**
     * @param null $cacheFlag
     *
     * @return bool
     */
    public function save($cacheFlag = null): bool
    {
        $time = time();
        $this->set('createdon', $time);
        $this->set('year', (int)date('Y', $time));
        $this->set('month', (int)date('m', $time));
        $this->set('day', (int)date('d', $time));

        return parent::save($cacheFlag);
    }
} 