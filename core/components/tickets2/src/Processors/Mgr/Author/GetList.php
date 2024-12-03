<?php

namespace Tickets2\Processors\Mgr\Author;

use MODX\Revolution\modUser;
use MODX\Revolution\modUserProfile;
use MODX\Revolution\Processors\Model\GetListProcessor;
use Tickets2\Model\TicketAuthor;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;

class GetList extends GetListProcessor
{
    public $classKey = TicketAuthor::class;
    public $defaultSortField = 'rating';
    public $defaultSortDirection = 'DESC';

    /**
     * @return bool
     */
    public function initialize()
    {
        $parent = parent::initialize();
        
        if ($this->getProperty('sort') == 'stars') {
            $dir = $this->getProperty('dir', 'DESC');
            $this->setProperty('sort', "stars_tickets2 {$dir}, stars_comments");
        }

        return $parent;
    }

    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $c->innerJoin(modUser::class, 'User');
        $c->innerJoin(modUserProfile::class, 'UserProfile');
        $c->select($this->modx->getSelectColumns($this->classKey, $this->classKey));
        $c->select([
            'username' => 'User.username',
            'fullname' => 'UserProfile.fullname',
            'active' => 'User.active',
            'blocked' => 'UserProfile.blocked',
        ]);

        if ($query = $this->getProperty('query', null)) {
            $c->where([
                'User.username:LIKE' => "%{$query}%",
                'OR:UserProfile.fullname:LIKE' => "%{$query}%",
            ]);
        }

        return $c;
    }

    /**
     * @param xPDOObject $object
     *
     * @return array
     */
    public function prepareRow(xPDOObject $object)
    {
        $array = parent::prepareRow($object);

        if (empty($array['fullname'])) {
            $array['fullname'] = $array['username'];
        }
        $array['stars'] = $array['stars_tickets2'] + $array['stars_comments'];

        return $array;
    }
} 