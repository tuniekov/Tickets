<?php

define('MODX_API_MODE', true);

/** @noinspection PhpIncludeInspection */
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/index.php';

use Tickets2\Model\TicketQueue;

/** @var modX $modx */
$modx->getService('error', 'error.modError');
$modx->getRequest();
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');
$modx->error->message = null;

$q = $modx->newQuery(TicketQueue::class);
$q->sortby('timestamp', 'ASC');
$queue = $modx->getCollection(TicketQueue::class, $q);

/** @var TicketQueue $letter */
foreach ($queue as $letter) {
    if ($letter->Send()) {
        $letter->remove();
    }
}
