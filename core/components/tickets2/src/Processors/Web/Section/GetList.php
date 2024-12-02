<?php

namespace Tickets2\Processors\Web\Section;

use MODX\Revolution\modAccessibleObject;
use MODX\Revolution\Processors\Model\GetListProcessor;
use Tickets2\Model\Tickets2Section;
use xPDO\Om\xPDOObject;
use xPDO\Om\xPDOQuery;

class GetList extends GetListProcessor
{
    public $classKey = Tickets2Section::class;
    public $defaultSortField = 'pagetitle';
    public $defaultSortDirection = 'ASC';
    private int $current_category = 0;

    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c): xPDOQuery
    {
        $context = array_map('trim', explode(',', $this->getProperty('context', $this->modx->context->key)));

        $c->where([
            'class_key' => Tickets2Section::class,
            'published' => 1,
            'deleted' => 0,
            'context_key:IN' => $context,
        ]);

        $sortby = $this->getProperty('sortby');
        $sortdir = $this->getProperty('sortdir');
        if ($sortby && $sortdir) {
            $c->sortby($sortby, $sortdir);
        }

        if (!empty($_REQUEST['tid']) && $tmp = $this->modx->getObject('Ticket', (int)$_REQUEST['tid'])) {
            $this->current_category = $tmp->get('parent');
        }

        if ($parents = $this->getProperty('parents')) {
            $depth = $this->getProperty('depth', 0);
            $parents = array_map('trim', explode(',', $parents));
            $parents_in = $parents_out = [];
            foreach ($parents as $v) {
                if (!is_numeric($v)) {
                    continue;
                }
                if ($v < 0) {
                    $parents_out[] = abs($v);
                } else {
                    $parents_in[] = abs($v);
                }
            }
            if (!empty($parents_in)) {
                foreach ($parents_in as $pid) {
                    $parents_in = array_merge($parents_in, $this->modx->getChildIds($pid, $depth));
                }
            }

            $parents = array_diff($parents_in, $parents_out);

            if (!empty($parents) && !empty($this->current_category)) {
                $c->where(['parent:IN' => $parents, 'OR:id:=' => $this->current_category]);
            } else if (!empty($parents)) {
                $c->where(['parent:IN' => $parents]);
            }

            if (!empty($parents_out)) {
                $c->where(['parent:NOT IN' => $parents_out]);
            }
        }
        if ($resources = $this->getProperty('resources')) {
            $resources = array_map('trim', explode(',', $resources));
            $resources_in = $resources_out = [];
            foreach ($resources as $r) {
                if (!is_numeric($r)) {
                    continue;
                }
                if ($r < 0) {
                    $resources_out[] = abs($r);
                } else {
                    $resources_in[] = abs($r);
                }
            }

            $resources = array_diff($resources_in, $resources_out);

            if (!empty($resources)) {
                $c->where(['id:IN' => $resources]);
            }

            if (!empty($resources_out)) {
                $c->where(['id:NOT IN' => $resources_out]);
            }
        }
        $c->prepare();

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
        /** @var xPDOObject|modAccessibleObject $object */
        foreach ($data['results'] as $object) {
            $check = $object instanceof modAccessibleObject &&
                !$object->checkPolicy(['section_add_children' => true]) &&
                $object->get('id') != $this->current_category;
            if ($check) {
                continue;
            }

            $objectArray = $this->prepareRow($object);
            if (!empty($objectArray) && is_array($objectArray)) {
                $list[] = $objectArray;
                $this->currentIndex++;
            }
        }
        $list = $this->afterIteration($list);

        return $list;
    }

    /**
     * @param xPDOObject $object
     *
     * @return array
     */
    public function prepareRow(xPDOObject $object): array
    {
        return $object->toArray();
    }
} 