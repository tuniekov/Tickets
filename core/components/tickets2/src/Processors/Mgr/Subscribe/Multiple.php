<?php

namespace Tickets2\Processors\Mgr\Subscribe;

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
        $parents = (int)$this->getProperty('parents');

        if (empty($ids) || empty($parents)) {
            return $this->success();
        }

        /** @var \Tickets2 $Tickets2 */
        $Tickets2 = $this->modx->getService('Tickets2');

        /** @var modProcessorResponse $response */
        $response = $Tickets2->runProcessor('mgr/subscribe/' . $method, ['ids' => $ids, 'parents' => $parents]);
        if ($response->isError()) {
            return $response->getResponse();
        }

        return $this->success();
    }
} 