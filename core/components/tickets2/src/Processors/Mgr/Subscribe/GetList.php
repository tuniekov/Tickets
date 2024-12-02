<?php

namespace Tickets2\Processors\Mgr\Subscribe;

use MODX\Revolution\modUser;
use MODX\Revolution\modUserProfile;
use MODX\Revolution\Processors\Model\GetListProcessor;
use Tickets2\Model\Tickets2Section;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;

class GetList extends GetListProcessor
{
    public $objectType = 'modUser';
    public $classKey = modUser::class;
    public $languageTopics = ['tickets2:default'];
    public $defaultSortField = 'modUser.id';
    public $defaultSortDirection = 'DESC';
    public array $subscribers = [];

    public function beforeQuery(): bool
    {
        $target = (int)$this->getProperty('parents');
        if ($section = $this->modx->getObject(Tickets2Section::class, $target, false)) {
            $properties = $section->get('properties');
        }

        $this->subscribers = !empty($properties['subscribers'])
            ? $properties['subscribers']
            : [];
        return true;
    }

    public function prepareQueryBeforeCount(xPDOQuery $c): xPDOQuery
    {
        $c->leftJoin(modUserProfile::class, 'Profile');
        if (empty($this->subscribers)) {
            $c->where([
                'modUser.id' => 0
            ]);
        } else {
            $where = [
                ['modUser.id:IN' => $this->subscribers]
            ];
            
            if ($query = $this->getProperty('query', null)) {
                $query = trim($query);
                if (is_numeric($query)) {
                    $where[]['modUser.id:='] = $query;
                } else {
                    $where[] = [
                        'Profile.fullname:LIKE' => '%' . $query . '%',
                        'OR:modUser.username:LIKE' => '%' . $query . '%',
                        'OR:Profile.email:LIKE' => '%' . $query . '%',
                    ];
                }
            }
            $c->where($where);
        }
        return $c;
    }

    public function prepareQueryAfterCount(xPDOQuery $c): xPDOQuery
    {
        $c->select($this->modx->getSelectColumns(modUser::class, 'modUser'));
        $c->select($this->modx->getSelectColumns(modUserProfile::class, 'Profile', '', ['fullname', 'email']));

        return $c;
    }

    /**
     * @param xPDOObject $object
     *
     * @return array
     */
    public function prepareRow(xPDOObject $object): array
    {
        $array = parent::prepareRow($object);

        unset($array['properties']);

        $array['actions'] = [];
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-gray',
            'title' => $this->modx->lexicon('tickets2_action_unsubscribe'),
            'multiple' => $this->modx->lexicon('tickets2_action_unsubscribe'),
            'action' => 'unsubscribeSection',
            'button' => true,
            'menu' => false,
        ];

        return $array;
    }
} 