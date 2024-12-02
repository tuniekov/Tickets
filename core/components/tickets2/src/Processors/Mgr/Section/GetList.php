<?php

namespace Tickets2\Processors\Mgr\Section;

use MODX\Revolution\modResource;
use MODX\Revolution\Processors\Model\GetListProcessor;
use Tickets2\Model\Tickets2Section;
use xPDO\Om\xPDOQuery;

class GetList extends GetListProcessor
{
    public $classKey = Tickets2Section::class;
    public $defaultSortField = 'pagetitle';
    public $defaultSortDirection = 'ASC';
    public $checkListPermission = true;
    public int $item_id = 0;

    /**
     * @return bool
     */
    public function initialize(): bool
    {
        if ($this->getProperty('combo') && !$this->getProperty('limit') && $id = $this->getProperty('id')) {
            $this->item_id = $id;
        }
        $this->setDefaultProperties([
            'start' => 0,
            'limit' => 20,
            'sort' => $this->defaultSortField,
            'dir' => $this->defaultSortDirection,
            'combo' => false,
            'query' => '',
        ]);

        return true;
    }

    /**
     * @return array|string
     */
    public function process()
    {
        $beforeQuery = $this->beforeQuery();
        if ($beforeQuery !== true) {
            return $this->failure($beforeQuery);
        }
        $data = $this->getData();
        $list = $this->iterate($data);

        return $this->outputArray($list, $data['total']);
    }

    /**
     * Get the data of the query
     *
     * @return array
     */
    public function getData(): array
    {
        $data = [];
        $limit = (int)$this->getProperty('limit');
        $start = (int)$this->getProperty('start');

        $c = $this->modx->newQuery($this->classKey);
        $c = $this->prepareQueryBeforeCount($c);
        $data['total'] = $this->modx->getCount($this->classKey, $c);
        $c = $this->prepareQueryAfterCount($c);

        $sortClassKey = $this->getSortClassKey();
        $sortKey = $this->modx->getSelectColumns($sortClassKey, $this->getProperty('sortAlias', $sortClassKey), '',
            [$this->getProperty('sort')]);
        if (empty($sortKey)) {
            $sortKey = $this->getProperty('sort');
        }
        $c->sortby($sortKey, $this->getProperty('dir'));
        if ($limit > 0) {
            $c->limit($limit, $start);
        }

        if ($c->prepare() && $c->stmt->execute()) {
            $data['results'] = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $data;
    }

    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c): xPDOQuery
    {
        $c->select('id,parent,pagetitle,context_key');
        $c->where([
            'class_key' => Tickets2Section::class,
        ]);

        if ($query = $this->getProperty('query')) {
            $c->where(['pagetitle:LIKE' => "%$query%"]);
        } else {
            if ($this->item_id) {
                $c->where(['id' => $this->item_id]);
            }
        }

        return $c;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function iterate(array $data): array
    {
        $list = [];
        $list = $this->beforeIteration($list);
        $this->currentIndex = 0;
        foreach ($data['results'] as $array) {
            $objectArray = $this->prepareResult($array);
            if (!empty($objectArray) && is_array($objectArray)) {
                $list[] = $objectArray;
                $this->currentIndex++;
            }
        }
        $list = $this->afterIteration($list);

        return $list;
    }

    /**
     * @param array $resourceArray
     *
     * @return array
     */
    public function prepareResult(array $resourceArray): array
    {
        $resourceArray['parents'] = [];
        $parents = $this->modx->getParentIds($resourceArray['id'], 2,
            ['context' => $resourceArray['context_key']]);
        if ($parents[count($parents) - 1] == 0) {
            unset($parents[count($parents) - 1]);
        }
        if (!empty($parents) && is_array($parents)) {
            $q = $this->modx->newQuery(modResource::class, ['id:IN' => $parents]);
            $q->select('id,pagetitle');
            if ($q->prepare() && $q->stmt->execute()) {
                while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                    $key = array_search($row['id'], $parents);
                    if ($key !== false) {
                        $parents[$key] = $row;
                    }
                }
            }
            $resourceArray['parents'] = array_reverse($parents);
        }

        return $resourceArray;
    }
} 