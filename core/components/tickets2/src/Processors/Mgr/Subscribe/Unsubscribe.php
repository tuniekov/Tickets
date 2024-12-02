<?php

namespace Tickets2\Processors\Mgr\Subscribe;

use MODX\Revolution\Processors\Processor;
use Tickets2\Model\Tickets2Section;

class Unsubscribe extends Processor
{
    public $classKey = Tickets2Section::class;
    public $languageTopics = ['tickets2'];
    public $permission = 'section_unsubscribe';

    /**
     * @return bool
     */
    public function checkPermissions(): bool
    {
        return $this->modx->hasPermission($this->permission);
    }

    /**
     * @return bool
     */
    public function process(): bool
    {
        $parents = $this->getProperty('parents');
        if ($section = $this->modx->getObject(Tickets2Section::class, $parents, false)) {
            $properties = $section->get('properties');
        }

        $arrUnsubscribe = $this->getProperty('ids');
        if (!empty($properties['subscribers'])) {
            $properties['subscribers'] = array_filter($properties['subscribers'], function($k) use($arrUnsubscribe, $parents) {
                if ($unsub = in_array($k, $arrUnsubscribe)) {
                    $this->logManagerAction($k, $parents);
                }
                return !$unsub;
            });
        }

        $section->set('properties', $properties);
        $section->save();

        return true;
    }

    /**
     * @param int $k
     * @param int $parents
     */
    public function logManagerAction(int $k, int $parents): void
    {
        $this->modx->logManagerAction('unsubscribe', $this->classKey, "{$parents} user {$k}");
    }
} 