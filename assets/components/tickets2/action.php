<?php
use MODX\Revolution\modX;
use MODX\Revolution\modResource;
use MODX\Revolution\modContext;
use Tickets2\Model\{TicketThread, Ticket};
use Tickets2\Tickets2;

if (empty($_REQUEST['action'])) {
    die('Access denied');
} else {
    $action = $_REQUEST['action'];
}

define('MODX_API_MODE', true);
/** @noinspection PhpIncludeInspection */
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/index.php';

$modx->getService('error', 'error.modError');
$modx->getRequest();
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');
$modx->error->message = null;

// Get properties
$properties = [];
/** @var TicketThread $thread */
if (!empty($_REQUEST['thread']) && $thread = $modx->getObject(TicketThread::class, ['name' => $_REQUEST['thread']])) {
    $properties = $thread->get('properties');
    if (!empty($_REQUEST['form_key']) && isset($_SESSION['TicketForm'][$_REQUEST['form_key']])) {
        $properties = array_merge($_SESSION['TicketForm'][$_REQUEST['form_key']], $properties);
    }
} elseif (!empty($_REQUEST['form_key']) && isset($_SESSION['TicketForm'][$_REQUEST['form_key']])) {
    $properties = $_SESSION['TicketForm'][$_REQUEST['form_key']];
} elseif (!empty($_SESSION['TicketForm'])) {
    $properties = $_SESSION['TicketForm'];
}

// Switch context
$context = 'web';
if (!empty($thread) && $thread->get('resource') && $object = $thread->getOne('Resource')) {
    $context = $object->get('context_key');
} elseif (!empty($_REQUEST['section']) && $object = $modx->getObject(modResource::class, (int)$_REQUEST['section'])) {
    $context = $object->get('context_key');
} elseif (!empty($_REQUEST['parent']) && $object = $modx->getObject(modResource::class, (int)$_REQUEST['parent'])) {
    $context = $object->get('context_key');
} elseif (!empty($_REQUEST['ctx']) && $object = $modx->getObject(modContext::class, ['key' => $_REQUEST['ctx']])) {
    $context = $object->get('key');
}
if ($context != 'web') {
    $modx->switchContext($context);
}

/** @var Tickets2 $Tickets2 */
define('MODX_ACTION_MODE', true);
$Tickets2 = $modx->getService('tickets2', Tickets2::class, 
    $modx->getOption('tickets2.core_path', null, $modx->getOption('core_path') . 'components/tickets2/') . 'model/tickets2/',
    $properties
);
if ($modx->error->hasError() || !($Tickets2 instanceof Tickets2)) {
    die('Error');
}

switch ($action) {
    case 'comment/preview':
        $response = $Tickets2->previewComment($_POST);
        break;
    case 'comment/save':
        $response = $Tickets2->saveComment($_POST);
        break;
    case 'comment/get':
        $response = $Tickets2->getComment((int)$_POST['id']);
        break;
    case 'comment/getlist':
        $response = $Tickets2->getNewComments($_POST['thread']);
        break;
    case 'comment/subscribe':
        $response = $Tickets2->subscribeThread($_POST['thread']);
        break;
    case 'comment/vote':
        $response = $Tickets2->voteComment((int)$_POST['id'], (int)$_POST['value']);
        break;
    case 'comment/star':
        $response = $Tickets2->starComment((int)$_POST['id']);
        break;
    case 'comment/file/upload':
        $response = $Tickets2->fileUploadComment($_POST, 'TicketComment');
        break;

    case 'ticket/draft':
    case 'ticket/publish':
    case 'ticket/update':
    case 'ticket/save':
        $response = $Tickets2->saveTicket($_POST);
        break;
    case 'ticket/preview':
        $response = $Tickets2->previewTicket($_POST);
        break;
    case 'ticket/vote':
        $response = $Tickets2->voteTicket((int)$_POST['id'], (int)$_POST['value']);
        break;
    case 'ticket/star':
        $response = $Tickets2->starTicket((int)$_POST['id']);
        break;
    case 'ticket/delete':
        $response = $Tickets2->deleteTicket(['id' => (int)$_POST['tid']]);
        break;
    case 'ticket/undelete':
        $response = $Tickets2->deleteTicket(['id' => (int)$_POST['tid']], true);
        break;

    case 'section/subscribe':
        $response = $Tickets2->subscribeSection((int)$_POST['section']);
        break;
    case 'author/subscribe':
        $response = $Tickets2->subscribeAuthor((int)$_POST['author']);
        break;

    case 'ticket/file/upload':
        $response = $Tickets2->fileUpload($_POST, 'Ticket');
        break;
    case 'ticket/file/delete':
        $response = $Tickets2->fileDelete((int)$_POST['id']);
        break;
    case 'ticket/file/sort':
        $response = $Tickets2->fileSort($_POST['rank']);
        break;
    default:
        $message = $_REQUEST['action'] != $action
            ? 'tickets2_err_register_globals'
            : 'tickets2_err_unknown';
        $response = json_encode([
            'success' => false,
            'message' => $modx->lexicon($message),
        ]);
}

if (is_array($response)) {
    $response = json_encode($response);
}

@session_write_close();
exit($response);