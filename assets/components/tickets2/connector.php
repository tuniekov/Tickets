<?php
use MODX\Revolution\modConnectorRequest;
/** @var  MODX\Revolution\modX $modx */
/** @var  Tickets2\Tickets2 $Tickets2 */

if (file_exists(dirname(__FILE__, 4) . '/config.core.php')) {
    require_once dirname(__FILE__, 4) . '/config.core.php';
} else {
    require_once dirname(__FILE__, 5) . '/config.core.php';
}

require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';
$Tickets2 = $modx->services->get('Tickets2');
$modx->lexicon->load('tickets2:default');

// handle request
$path = $modx->getOption(
    'processorsPath',
    $Tickets2->config,
    $modx->getOption('core_path') . 'components/tickets2/' . 'src/Processors/'
);
$modx->getRequest();

/** @var MODX\Revolution\modConnectorRequest $request */
$request = $modx->request;
$request->handleRequest([
    'processors_path' => $path,
    'location' => '',
]);