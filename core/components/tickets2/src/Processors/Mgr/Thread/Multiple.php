<?php

namespace Tickets2\Processors\Mgr\Thread;

use MODX\Revolution\Processors\Processor;
use MODX\Revolution\modProcessorResponse;

class Multiple extends Processor
{
    /**
     * @return array|string
     */
    public function process()
    {
        if (!$method = $this->getProperty('method', false)) {
            return $this->failure();
        }
        $ids = json_decode($this->getProperty('ids'), true);
        if (empty($ids)) {
            return $this->success();
        }

        /** @var \Tickets2 $Tickets2 */
        $Tickets2 = $this->modx->getService('Tickets2');

        foreach ($ids as $id) {
            /** @var modProcessorResponse $response */
            $response = $Tickets2->runProcessor('mgr/thread/' . $method, ['id' => $id]);
            if ($response->isError()) {
                return $response->getResponse();
            }
        }

        return $this->success();
    }
} 