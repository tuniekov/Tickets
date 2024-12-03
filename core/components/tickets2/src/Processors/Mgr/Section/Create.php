<?php

namespace Tickets2\Processors\Mgr\Section;

use MODX\Revolution\Processors\Resource\Create as ResourceCreate;
use Tickets2\Model\Tickets2Section;

class Create extends ResourceCreate
{
    public $classKey = Tickets2Section::class;

    /**
     * @return array|string
     */
    public function beforeSet()
    {
        $this->setProperties([
            'isfolder' => 1,
        ]);

        return parent::beforeSet();
    }

    /**
     * @return string
     */
    public function prepareAlias()
    {
        if ($this->workingContext->getOption('tickets2.section_id_as_alias')) {
            $alias = 'empty';
            $this->setProperty('alias', $alias);
        } else {
            $alias = parent::prepareAlias();
        }

        return $alias;
    }

    /**
     * @return mixed
     */
    public function afterSave()
    {
        if ($this->object->alias == 'empty') {
            $this->object->set('alias', $this->object->id);
            $this->object->save();
        }

        // Updating resourceMap before OnDocSaveForm event
        $results = $this->modx->cacheManager->generateContext($this->object->context_key);
        if (isset($results['resourceMap'])) {
            $this->modx->context->resourceMap = $results['resourceMap'];
        }
        if (isset($results['aliasMap'])) {
            $this->modx->context->aliasMap = $results['aliasMap'];
        }
        $this->handleProperties();

        return parent::afterSave();
    }

    /**
     * Handle boolean properties
     */
    protected function handleProperties()
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
        $this->setProperty('properties', $properties);
    }
} 