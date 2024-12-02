<?php

namespace Tickets2\Processors\Mgr\Section;

use MODX\Revolution\Processors\Resource\Update as ResourceUpdate;
use Tickets2\Model\Tickets2Section;

class Update extends ResourceUpdate
{
    public $classKey = Tickets2Section::class;

    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        $primaryKey = $this->getProperty($this->primaryKeyField, false);
        if (empty($primaryKey)) {
            return $this->modx->lexicon($this->objectType . '_err_ns');
        }

        if (!$this->modx->getCount($this->classKey, [
                'id' => $primaryKey,
                'class_key' => $this->classKey,
            ]) && $res = $this->modx->getObject('modResource', ['id' => $primaryKey])
        ) {
            $res->set('class_key', $this->classKey);
            $res->save();
        }

        return parent::initialize();
    }

    /**
     * @return int|mixed|string
     */
    public function checkFriendlyAlias(): mixed
    {
        if ($this->workingContext->getOption('tickets2.section_id_as_alias')) {
            $alias = $this->object->id;
            $this->setProperty('alias', $alias);
        } else {
            $alias = parent::checkFriendlyAlias();
        }

        return $alias;
    }

    /**
     * @return bool|string
     */
    public function beforeSet()
    {
        $this->setProperties([
            'isfolder' => 1,
            'hide_children_in_tree' => 0,
        ]);

        $this->handleProperties();

        return parent::beforeSet();
    }

    /**
     * Handle boolean properties
     */
    protected function handleProperties(): void
    {
        $properties = $this->getProperty('properties');
        if (!empty($properties['tickets2'])) {
            foreach ($properties['tickets2'] as &$property) {
                if ($property == 'true') {
                    $property = true;
                } elseif ($property == 'false') {
                    $property = false;
                }
            }
        }
        //pass array subscribers from previous state
        $old_properties = $this->object->get('properties');
        if (!empty($old_properties['subscribers'])) {
            $properties['subscribers'] = $old_properties['subscribers'];
        }
        $properties['syncsite'] = $this->getProperty('syncsite');
        $properties = array_merge($this->object->getProperties(), $properties);
        $this->setProperty('properties', $properties);
    }
} 