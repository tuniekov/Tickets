<?php
use MODX\Revolution\modX;
use Tickets2\Model\{Ticket, TicketThread, TicketComment, TicketVote, TicketStar};
use Tickets2\Tickets2;

/** @var modX $modx */
/** @var array $scriptProperties */

if (empty($thread)) {
    $scriptProperties['thread'] = $modx->getOption('thread', $scriptProperties, 'resource-' . $modx->resource->id, true);
}
$scriptProperties['resource'] = $modx->resource->get('id');
$scriptProperties['snippetPrepareComment'] = $modx->getOption('tickets2.snippet_prepare_comment');
$scriptProperties['commentEditTime'] = $modx->getOption('tickets2.comment_edit_time', null, 180);

$depth = $modx->getOption('depth', $scriptProperties, 0);
$tplComments = $modx->getOption('tplComments', $scriptProperties, 'tpl.Tickets2.comment.wrapper');
$tplCommentForm = $modx->getOption('tplCommentForm', $scriptProperties, 'tpl.Tickets2.comment.form');
$tplCommentFormGuest = $modx->getOption('tplCommentFormGuest', $scriptProperties, 'tpl.Tickets2.comment.form.guest');
$tplCommentAuth = $modx->getOption('tplCommentAuth', $scriptProperties, 'tpl.Tickets2.comment.one.auth');
$tplCommentGuest = $modx->getOption('tplCommentGuest', $scriptProperties, 'tpl.Tickets2.comment.one.guest');
$tplLoginToComment = $modx->getOption('tplLoginToComment', $scriptProperties, 'tpl.Tickets2.comment.login');
$outputSeparator = $modx->getOption('outputSeparator', $scriptProperties, "\n");

/** @var Tickets2 $Tickets2 */
$Tickets2 = $modx->getService('tickets2', Tickets2::class, 
    $modx->getOption('tickets2.core_path', null, $modx->getOption('core_path') . 'components/tickets2/') . 'model/tickets2/',
    $scriptProperties
);
$Tickets2->initialize($modx->context->key, $scriptProperties);

$tplFiles = $Tickets2->config['tplFiles'] = $modx->getOption('tplFiles', $scriptProperties, 'tpl.Tickets2.comment.form.files');
$tplFile = $Tickets2->config['tplFile'] = $modx->getOption('tplFile', $scriptProperties, 'tpl.Tickets2.form.file', true);
$tplImage = $Tickets2->config['tplImage'] = $modx->getOption('tplImage', $scriptProperties, 'tpl.Tickets2.form.image', true);

/** @var pdoFetch $pdoFetch */
$pdoFetch = $modx->getService('pdoFetch');
$pdoFetch->setConfig($scriptProperties);
$pdoFetch->addTime('pdoTools loaded');

// Prepare Ticket Thread
/** @var TicketThread $thread */
if (!$thread = $modx->getObject(TicketThread::class, ['name' => $scriptProperties['thread']])) {
    $thread = $modx->newObject(TicketThread::class);
    $thread->fromArray([
        'name' => $scriptProperties['thread'],
        'resource' => $modx->resource->get('id'),
        'createdby' => $modx->user->id,
        'createdon' => date('Y-m-d H:i:s'),
        'subscribers' => [$modx->resource->get('createdby')],
    ]);
} elseif ($thread->get('deleted')) {
    return $modx->lexicon('ticket_thread_err_deleted');
}
// Prepare session for guests
if (!empty($allowGuest) && !isset($_SESSION['TicketComments'])) {
    $_SESSION['TicketComments'] = ['name' => '', 'email' => '', 'ids' => []];
}

// Migrate authors to subscription system
if (!is_array($thread->get('subscribers'))) {
    $thread->set('subscribers', [$modx->resource->get('createdby')]);
}
$thread->set('resource', $modx->resource->get('id'));
$thread->set('properties', $scriptProperties);
$thread->save();

$ratings = [];
/** @var Ticket $ticket */
if ($ticket = $thread->getOne('Ticket')) {
    /** @var Tickets2Section $section */
    if ($section = $ticket->getOne('Section')) {
        $ratings = $section->getProperties('ratings');
    }
}

// Prepare query to db
$class = TicketComment::class;
$where = array();
if (empty($showUnpublished)) {
    $where['published'] = 1;
}

// Joining tables
$innerJoin = [
    'Thread' => [
        'class' => TicketThread::class,
        'on' => '`Thread`.`id` = `TicketComment`.`thread` AND `Thread`.`name` = "' . $thread->get('name') . '"',
    ],
];
$leftJoin = [
    'User' => ['class' => 'MODX\Revolution\modUser', 'on' => '`User`.`id` = `TicketComment`.`createdby`'],
    'Profile' => ['class' => 'MODX\Revolution\modUserProfile', 'on' => '`Profile`.`internalKey` = `TicketComment`.`createdby`'],
];
if ($Tickets2->authenticated) {
    $leftJoin['Vote'] = [
        'class' => TicketVote::class,
        'on' => '`Vote`.`id` = `TicketComment`.`id` AND `Vote`.`class` = "TicketComment" AND `Vote`.`createdby` = ' . $modx->user->id,
    ];
    $leftJoin['Star'] = [
        'class' => TicketStar::class,
        'on' => '`Star`.`id` = `TicketComment`.`id` AND `Star`.`class` = "TicketComment" AND `Star`.`createdby` = ' . $modx->user->id,
    ];
}
// Fields to select
$select = [
    'TicketComment' => $modx->getSelectColumns(TicketComment::class, 'TicketComment', '', ['raw'], true) .
        ', `parent` as `new_parent`',
    'Thread' => '`Thread`.`resource`',
    'User' => '`User`.`username`',
    'Profile' => $modx->getSelectColumns('MODX\Revolution\modUserProfile', 'Profile', '', ['id', 'email'], true) . ',`Profile`.`email` as `user_email`',
];
if ($Tickets2->authenticated) {
    $select['Vote'] = '`Vote`.`value` as `vote`';
    $select['Star'] = 'COUNT(`Star`.`id`) as `star`';
}

// Add custom parameters
foreach (['where', 'select', 'leftJoin', 'innerJoin'] as $v) {
    if (!empty($scriptProperties[$v])) {
        $tmp = $scriptProperties[$v];
        if (!is_array($tmp)) {
            $tmp = json_decode($tmp, true);
        }
        if (is_array($tmp)) {
            $$v = array_merge($$v, $tmp);
        }
    }
    unset($scriptProperties[$v]);
}
$pdoFetch->addTime('Conditions prepared');

$default = [
    'class' => $class,
    'where' => json_encode($where),
    'innerJoin' => json_encode($innerJoin),
    'leftJoin' => json_encode($leftJoin),
    'select' => json_encode($select),
    'sortby' => $class . '.id',
    'sortdir' => 'ASC',
    'groupby' => $class . '.id',
    'limit' => 0,
    'fastMode' => true,
    'return' => 'data',
    'nestedChunkPrefix' => 'tickets2_',
];

// Merge all properties and run!
$pdoFetch->setConfig(array_merge($default, $scriptProperties), false);
$pdoFetch->addTime('Query parameters prepared.');
$rows = $pdoFetch->run();

// Processing rows
$output = $commentsThread = null;
if (!empty($rows) && is_array($rows)) {
    $tmp = [];
    $i = 1;
    foreach ($rows as $row) {
        $row['ratings'] = $ratings;
        $row['idx'] = $i++;
        $tmp[$row['id']] = $row;
    }
    $rows = $thread->buildTree($tmp, $depth);
    unset($tmp, $i);

    if (!empty($formBefore)) {
        $rows = array_reverse($rows);
    }

    $tpl = !$thread->get('closed') && ($Tickets2->authenticated || !empty($allowGuest))
        ? $tplCommentAuth
        : $tplCommentGuest;
    foreach ($rows as $row) {
        $output[] = $Tickets2->templateNode($row, $tpl);
    }

    $pdoFetch->addTime('Returning processed chunks');
    $output = implode($outputSeparator, $output);
}

$commentsThread = $pdoFetch->getChunk($tplComments, [
    'total' => $modx->getPlaceholder($pdoFetch->config['totalVar']),
    'comments' => $output,
    'subscribed' => $thread->isSubscribed(),
]);

$pls = ['thread' => $scriptProperties['thread']];

if (!empty($allowFiles)) {
    if ($Tickets2->authenticated) {
        if (empty($source)) {
            $source = $Tickets2->config['source'] = $modx->getOption('tickets2.source_default', null,
                $modx->getOption('default_media_source'));
        }

        $pls['files'] = $Tickets2->getFileComment();

        /** @var MODX\Revolution\Sources\modMediaSource $source */
        if ($source = $modx->getObject('MODX\Revolution\Sources\modMediaSource', ['id' => $source])) {
            $properties = $source->getPropertyList();
            $config = [
                'size' => !empty($properties['maxUploadSize'])
                    ? $properties['maxUploadSize']
                    : 3145728,
                'height' => !empty($properties['maxUploadHeight'])
                    ? $properties['maxUploadHeight']
                    : 1080,
                'width' => !empty($properties['maxUploadWidth'])
                    ? $properties['maxUploadWidth']
                    : 1920,
                'extensions' => !empty($properties['allowedFileTypes'])
                    ? $properties['allowedFileTypes']
                    : 'jpg,jpeg,png,gif',
            ];
            $modx->regClientStartupScript('<script type="text/javascript">Tickets2Config.source=' . json_encode($config) . ';</script>',
                true);
        }
        $modx->regClientScript($Tickets2->config['jsUrl'] . 'web/lib/plupload/plupload.full.min.js');
        $modx->regClientScript($Tickets2->config['jsUrl'] . 'web/files.js');

        $lang = $modx->getOption('cultureKey');
        if ($lang != 'en' && file_exists($Tickets2->config['jsPath'] . 'web/lib/plupload/i18n/' . $lang . '.js')) {
            $modx->regClientScript($Tickets2->config['jsUrl'] . 'web/lib/plupload/i18n/' . $lang . '.js');
        }
    }
    else {
        $pls['allowFiles'] = 1;
    }
}

$key = md5(json_encode($Tickets2->config));
$_SESSION['TicketForm'][$key] = $Tickets2->config;
$pls['formkey'] = $key;

if (!$Tickets2->authenticated && empty($allowGuest)) {
    $form = $pdoFetch->getChunk($tplLoginToComment);
} elseif (!$Tickets2->authenticated) {
    $pls['name'] = $_SESSION['TicketComments']['name'];
    $pls['email'] = $_SESSION['TicketComments']['email'];
    if (!empty($enableCaptcha)) {
        $tmp = $Tickets2->getCaptcha();
        $pls['captcha'] = $modx->lexicon('ticket_comment_captcha', $tmp);
    }
    $form = $pdoFetch->getChunk($tplCommentFormGuest, $pls);
} else {
    $form = $pdoFetch->getChunk($tplCommentForm, $pls);
}

$commentForm = $thread->get('closed')
    ? $modx->lexicon('ticket_thread_err_closed')
    : $form;
$output = !empty($formBefore)
    ? $commentForm . $commentsThread
    : $commentsThread . $commentForm;

if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
    $output .= '<pre class="CommentsLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

$modx->regClientStartupScript(
    '<script type="text/javascript">
        Tickets2Config.formBefore = ' . (int)!empty($formBefore) . ';
        Tickets2Config.thread_depth = ' . (int)$depth . ';
    </script>',
    true
);

// Return output
if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $output);
    if (!empty($separatePlaceholder)) {
        $modx->setPlaceholder($toPlaceholder.'_form', $commentForm);
        $modx->setPlaceholder($toPlaceholder.'_thread', $commentsThread);
    }
} else {
    return $output;
}