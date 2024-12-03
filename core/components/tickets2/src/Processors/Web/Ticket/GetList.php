<?php

namespace Tickets2\Processors\Web\Ticket;

use MODX\Revolution\Processors\Model\GetListProcessor;
use Tickets2\Model\Ticket;
use xPDO\Om\xPDOQuery;

class GetList extends GetListProcessor
{
    public $classKey = Ticket::class;
    public $languageTopics = ['tickets2:default'];
    public $defaultSortField = 'createdon';
    public $defaultSortDirection = 'DESC';

    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        if ($parents = $this->getProperty('parents')) {
            if (!is_array($parents)) {
                $parents = explode(',', $parents);
            }
            $c->where(['parent:IN' => $parents]);
        }

        $c->where([
            'class_key' => Ticket::class,
            'published' => 1,
            'deleted' => 0,
        ]);

        return $c;
    }
} 