<?php

namespace Tickets2\Processors\Mgr\Thread;

use MODX\Revolution\Processors\Model\GetProcessor;
use Tickets2\Model\TicketThread;

class Get extends GetProcessor
{
    public $objectType = 'TicketThread';
    public $classKey = TicketThread::class;
    public $languageTopics = ['tickets2:default'];

    /**
     * @return bool|string
     */
    public function initialize(): bool|string
    {
        $where = [];
        if ($id = (int)$this->getProperty('id')) {
            $where['id'] = $id;
        } elseif ($res = (int)$this->getProperty('resource')) {
            $where['resource'] = $res;
        }
        if (!$this->object = $this->modx->getObject($this->classKey, $where)) {
            if (!empty($res)) {
                $this->object = $this->modx->newObject($this->classKey);
                $this->object->fromArray([
                    'name' => 'resource-' . $res,
                    'createdby' => $this->modx->user->id,
                    'createdon' => date('Y-m-d H:i:s'),
                    'resource' => $res,
                    'subscribers' => [$this->modx->user->id],
                ]);
                $this->object->save();
            } else {
                return $this->modx->lexicon('ticket_thread_err_nf');
            }
        } elseif ($this->object->get('deleted')) {
            return $this->modx->lexicon('ticket_thread_err_deleted');
        } elseif ($this->object->get('closed')) {
            return $this->modx->lexicon('ticket_thread_err_closed');
        }

        return true;
    }

    /**
     * @return array|string
     */
    public function cleanup(): array
    {
        $thread = $this->object->toArray();
        return $this->success('', $thread);
    }
} 