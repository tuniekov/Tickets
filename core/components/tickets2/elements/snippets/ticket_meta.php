<?php
use MODX\Revolution\modX;
use MODX\Revolution\modResource;
use Tickets2\Model\{Ticket, TicketTotal, TicketVote, TicketStar, Tickets2Section};
use Tickets2\Tickets2;

/** @var modX $modx */
/** @var array $scriptProperties */

/** @var Tickets2 $Tickets2 */
$Tickets2 = $modx->getService('tickets2', Tickets2::class, 
    $modx->getOption('tickets2.core_path', null, $modx->getOption('core_path') . 'components/tickets2/') . 'model/tickets2/',
    $scriptProperties
);
$Tickets2->initialize($modx->context->key, $scriptProperties);

$scriptProperties['nestedChunkPrefix'] = 'tickets2_';
/** @var pdoFetch $pdoFetch */
$pdoFetch = $modx->getService('pdoFetch');
$pdoFetch->setConfig($scriptProperties);
$pdoFetch->addTime('pdoTools loaded');

if (empty($id)) {
    $id = $modx->resource->id;
}
/** @var Ticket|modResource $ticket */
if (!$ticket = $modx->getObject(modResource::class, ['id' => $id])) {
    return 'Could not load resource with id = ' . $id;
}

$class = $ticket instanceof Ticket
    ? Ticket::class
    : modResource::class;

/** @var TicketTotal $total */
if ($class == Ticket::class && $total = $ticket->getOne('Total')) {
    $total->fetchValues();
    $total->save();
}
$data = $ticket->toArray();
$data['date_ago'] = $Tickets2->dateFormat($data['createdon']);

$vote = $pdoFetch->getObject(TicketVote::class,
    [
        'id' => $ticket->id,
        'class' => 'Ticket',
        'createdby' => $modx->user->id,
    ],
    [
        'select' => 'value',
        'sortby' => 'id',
    ]
);
if (!empty($vote)) {
    $data['vote'] = $vote['value'];
}

$star = $modx->getCount(TicketStar::class, ['id' => $ticket->id, 'class' => 'Ticket', 'createdby' => $modx->user->id]);
$data['stared'] = !empty($star);
$data['unstared'] = empty($star);

if ($class != Ticket::class) {
    // Rating
    if (!$modx->user->id || $modx->user->id == $ticket->createdby) {
        $data['voted'] = 0;
    } else {
        $q = $modx->newQuery(TicketVote::class);
        $q->where(['id' => $ticket->id, 'createdby' => $modx->user->id, 'class' => 'Ticket']);
        $q->select('`value`');
        $tstart = microtime(true);
        if ($q->prepare() && $q->stmt->execute()) {
            $modx->startTime += microtime(true) - $tstart;
            $modx->executedQueries++;
            $voted = $q->stmt->fetchColumn();
            if ($voted > 0) {
                $voted = 1;
            } elseif ($voted < 0) {
                $voted = -1;
            }
            $data['voted'] = $voted;
        }
    }
    $data['can_vote'] = $data['voted'] === false && $Tickets2->authenticated && $modx->user->id != $ticket->createdby;
    $data = array_merge($ticket->getProperties('tickets2'), $data);
    if (!isset($data['rating'])) {
        $data['rating'] = $data['rating_total'] = $data['rating_plus'] = $data['rating_minus'] = 0;
    }

    // Views
    $data['views'] = $modx->getCount('TicketView', ['parent' => $ticket->id]);

    // Comments
    $data['comments'] = 0;
    $thread = empty($thread)
        ? 'resource-' . $ticket->id
        : $thread;
    $q = $modx->newQuery('TicketThread', ['name' => $thread]);
    $q->leftJoin('TicketComment', 'TicketComment',
        "TicketThread.id = TicketComment.thread AND TicketComment.published = 1"
    );
    $q->select('COUNT(`TicketComment`.`id`) as `comments`');
    $tstart = microtime(true);
    if ($q->prepare() && $q->stmt->execute()) {
        $modx->startTime += microtime(true) - $tstart;
        $modx->executedQueries++;
        $data['comments'] = (int)$q->stmt->fetchColumn();
    }

    // Stars
    $data['stars'] = $modx->getCount(TicketStar::class, ['id' => $ticket->id, 'class' => 'Ticket']);
}

if ($data['rating'] > 0) {
    $data['rating'] = '+' . $data['rating'];
    $data['rating_positive'] = 1;
} elseif ($data['rating'] < 0) {
    $data['rating_negative'] = 1;
}
$data['rating_total'] = abs($data['rating_plus']) + abs($data['rating_minus']);

/** @var Tickets2Section $section */
if ($section = $modx->getObject(Tickets2Section::class, ['id' => $ticket->parent])) {
    $data = array_merge($data, $section->toArray('section.'));
}
if (isset($data['section.properties']['ratings']['days_ticket_vote'])) {
    if ($data['section.properties']['ratings']['days_ticket_vote'] !== '') {
        $max = strtotime($data['createdon']) + ((float)$data['section.properties']['ratings']['days_ticket_vote'] * 86400);
        if (time() > $max) {
            $data['cant_vote'] = 1;
        }
    }
}
if (!isset($data['cant_vote'])) {
    if (!$Tickets2->authenticated || $modx->user->id == $ticket->createdby) {
        $data['cant_vote'] = 1;

    } elseif (array_key_exists('vote', $data)) {
        if ($data['vote'] == '') {
            $data['can_vote'] = 1;
        } elseif ($data['vote'] > 0) {
            $data['voted_plus'] = 1;
            $data['cant_vote'] = 1;
        } elseif ($data['vote'] < 0) {
            $data['voted_minus'] = 1;
            $data['cant_vote'] = 1;
        } else {
            $data['voted_none'] = 1;
            $data['cant_vote'] = 1;
        }
    } else {
        $data['can_vote'] = 1;
    }
}

$data['active'] = (int)!empty($data['can_vote']);
$data['inactive'] = (int)!empty($data['cant_vote']);
$data['can_star'] = $Tickets2->authenticated;

if (!empty($getUser)) {
    $fields = $modx->getFieldMeta('modUserProfile');
    $user = $pdoFetch->getObject('modUserProfile', ['internalKey' => $ticket->createdby], [
        'innerJoin' => [
            'modUser' => ['class' => 'modUser', 'on' => 'modUserProfile.internalKey = modUser.id'],
        ],
        'select' => [
            'modUserProfile' => implode(',', array_keys($fields)),
            'modUser' => 'username',
        ],
    ]);
    if ($user) {
        $data = array_merge($data, $user);
    }
}

if (!empty($getFiles)) {
    $where = ['deleted' => 0, 'class' => 'Ticket', 'parent' => $ticket->id];
    $collection = $pdoFetch->getCollection('TicketFile', $where, ['sortby' => 'createdon', 'sortdir' => 'ASC']);
    $data['files'] = $content = '';
    if (!empty($unusedFiles)) {
        $content = $ticket->getContent();
    }
    foreach ($collection as $item) {
        if ($content && strpos($content, $item['url']) !== false) {
            continue;
        }
        $item['size'] = round($item['size'] / 1024, 2);
        $data['files'] .= !empty($tplFile)
            ? $Tickets2->getChunk($tplFile, $item)
            : $Tickets2->getChunk('', $item);
    }
    $data['has_files'] = !empty($data['files']);
}
$data['id'] = $ticket->get('id');

return !empty($tpl)
    ? $Tickets2->getChunk($tpl, $data)
    : $Tickets2->getChunk('', $data);
