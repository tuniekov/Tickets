<?php

namespace Tickets2\Processors\Web\Ticket;

use MODX\Revolution\modResource;
use MODX\Revolution\Processors\Processor;
use Tickets2\Model\TicketStar;

class Star extends Processor
{
    public $classKey = TicketStar::class;
    public $permission = 'ticket_star';

    /**
     * @return bool|null|string
     */
    public function initialize(): bool|string|null
    {
        if (!$this->modx->hasPermission($this->permission)) {
            return $this->modx->lexicon('access_denied');
        }

        return true;
    }

    /**
     * @return array|string
     */
    public function process(): array|string
    {
        $id = (int)$this->getProperty('id');

        /** @var modResource $object */
        if (!$object = $this->modx->getObject(modResource::class, $id)) {
            return $this->failure($this->modx->lexicon('ticket_err_id', ['id' => $id]));
        }

        $data = [
            'id' => $id,
            'class' => 'Ticket',
            'createdby' => $this->modx->user->id,
        ];

        /** @var TicketStar $star */
        if ($star = $this->modx->getObject($this->classKey, $data)) {
            $event = $this->modx->invokeEvent('OnBeforeTicketUnStar', [
                $this->objectType => &$star,
                'object' => &$star,
            ]);
            if (is_array($event) && !empty($event)) {
                return $this->failure(implode("\n", $event));
            }

            $star->remove();

            $this->modx->invokeEvent('OnTicketUnStar', [
                $this->objectType => &$star,
                'object' => &$star,
            ]);
        } else {
            $star = $this->modx->newObject($this->classKey);
            $data['owner'] = $object->get('createdby');
            $data['createdon'] = date('Y-m-d H:i:s');

            $event = $this->modx->invokeEvent('OnBeforeTicketStar', [
                $this->objectType => &$star,
                'object' => &$star,
            ]);
            if (is_array($event) && !empty($event)) {
                return $this->failure(implode("\n", $event));
            }

            $star->fromArray($data, '', true, true);
            $star->save();

            $this->modx->invokeEvent('OnTicketStar', [
                $this->objectType => &$star,
                'object' => &$star,
            ]);
        }

        $stars = $this->modx->getCount(TicketStar::class, ['id' => $id, 'class' => 'Ticket']);

        return $this->success('', ['stars' => $stars]);
    }
} 