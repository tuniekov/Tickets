<?php
use MODX\Revolution\modX;
use MODX\Revolution\modUser;
use MODX\Revolution\modUserProfile;
use Tickets2\Model\{Ticket, TicketThread, TicketComment, Tickets2Section};
use Tickets2\Tickets2;

/** @var modX $modx */
/** @var array $scriptProperties */

if (!empty($cacheKey) && $output = $modx->cacheManager->get('tickets2/latest.' . $cacheKey)) {
    return $output;
}

/** @var Tickets2 $Tickets2 */
$Tickets2 = $modx->getService('tickets2', Tickets2::class, 
    $modx->getOption('tickets2.core_path', null, $modx->getOption('core_path') . 'components/tickets2/') . 'model/tickets2/',
    $scriptProperties
);
$Tickets2->initialize($modx->context->key, $scriptProperties);

/** @var pdoFetch $pdoFetch */
$pdoFetch = $modx->getService('pdoFetch');
$pdoFetch->setConfig($scriptProperties);
$pdoFetch->addTime('pdoTools loaded');

if (empty($action)) {
    $action = 'comments';
}
if ($action == 'tickets2' && $scriptProperties['tpl'] == 'tpl.Tickets2.comment.latest') {
    $scriptProperties['tpl'] = 'tpl.Tickets2.ticket.latest';
}
$action = strtolower($action);
$where = $action == 'tickets2'
    ? ['class_key' => Ticket::class]
    : [];

if (empty($showUnpublished)) {
    $where['Ticket.published'] = 1;
}
if (empty($showHidden)) {
    $where['Ticket.hidemenu'] = 0;
}
if (empty($showDeleted)) {
    $where['Ticket.deleted'] = 0;
}
if (!isset($cacheTime)) {
    $cacheTime = 1800;
}
if (!empty($user)) {
    $user = array_map('trim', explode(',', $user));
    $user_id = $user_username = [];
    foreach ($user as $v) {
        if (is_numeric($v)) {
            $user_id[] = $v;
        } else {
            $user_username[] = $v;
        }
    }
    if (!empty($user_id) && !empty($user_username)) {
        $where[] = '(`User`.`id` IN (' . implode(',', $user_id) . ') OR `User`.`username` IN (\'' . implode('\',\'',
                $user_username) . '\'))';
    } else {
        if (!empty($user_id)) {
            $where['User.id:IN'] = $user_id;
        } else {
            if (!empty($user_username)) {
                $where['User.username:IN'] = $user_username;
            }
        }
    }
}

// Filter by ids
if (!empty($resources)) {
    $resources = array_map('trim', explode(',', $resources));
    $in = $out = [];
    foreach ($resources as $v) {
        if (!is_numeric($v)) {
            continue;
        }
        if ($v < 0) {
            $out[] = abs($v);
        } else {
            $in[] = $v;
        }
    }
    if (!empty($in)) {
        $where['id:IN'] = $in;
    }
    if (!empty($out)) {
        $where['id:NOT IN'] = $out;
    }
} // Filter by parents
else {
    if (!empty($parents) && $parents > 0) {
        $pids = array_map('trim', explode(',', $parents));
        $parents = $pids;
        if (!empty($depth) && $depth > 0) {
            foreach ($pids as $v) {
                if (!is_numeric($v)) {
                    continue;
                }
                $parents = array_merge($parents, $modx->getChildIds($v, $depth));
            }
        }
        if (!empty($parents)) {
            $where['Ticket.parent:IN'] = $parents;
        }
    }
}

// Joining tables
if ($action == 'comments') {
    $class = TicketComment::class;

    $innerJoin = [];
    $innerJoin['Thread'] = empty($user)
        ? [
            'class' => TicketThread::class,
            'on' => '`TicketComment`.`id` = `Thread`.`comment_last` AND `Thread`.`deleted` = 0',
        ]
        : [
            'class' => TicketThread::class,
            'on' => '`TicketComment`.`thread` = `Thread`.`id` AND `Thread`.`deleted` = 0',
        ];
    $innerJoin['Ticket'] = ['class' => Ticket::class, 'on' => '`Ticket`.`id` = `Thread`.`resource`'];

    $leftJoin = [
        'Section' => ['class' => Tickets2Section::class, 'on' => '`Section`.`id` = `Ticket`.`parent`'],
        'User' => ['class' => modUser::class, 'on' => '`User`.`id` = `TicketComment`.`createdby`'],
        'Profile' => [
            'class' => modUserProfile::class,
            'on' => '`Profile`.`internalKey` = `TicketComment`.`createdby`',
        ],
    ];

    $select = [
        'TicketComment' => !empty($includeContent)
            ? $modx->getSelectColumns(TicketComment::class, 'TicketComment', '', ['raw'], true)
            : $modx->getSelectColumns(TicketComment::class, 'TicketComment', '', ['text', 'raw'], true),
        'Ticket' => !empty($includeContent)
            ? $modx->getSelectColumns(Ticket::class, 'Ticket', 'ticket.')
            : $modx->getSelectColumns(Ticket::class, 'Ticket', 'ticket.', ['content'], true),
        'Thread' => '`Thread`.`comments`',
    ];
    $groupby = empty($user)
        ? '`Ticket`.`id`'
        : '`TicketComment`.`id`';
    $where['TicketComment.deleted'] = 0;
} elseif ($action == 'tickets2') {
    $class = Ticket::class;

    $innerJoin = [];
    $leftJoin = [
        'Thread' => [
            'class' => TicketThread::class,
            'on' => '`Thread`.`resource` = `Ticket`.`id` AND `Thread`.`deleted` = 0',
        ],
        'Section' => ['class' => Tickets2Section::class, 'on' => '`Section`.`id` = `Ticket`.`parent`'],
        'User' => ['class' => modUser::class, 'on' => '`User`.`id` = `Ticket`.`createdby`'],
        'Profile' => ['class' => modUserProfile::class, 'on' => '`Profile`.`internalKey` = `Ticket`.`createdby`'],
    ];

    $select = [
        'Ticket' => !empty($includeContent)
            ? $modx->getSelectColumns(Ticket::class, 'Ticket')
            : $modx->getSelectColumns(Ticket::class, 'Ticket', '', ['content'], true),
        'Thread' => '`Thread`.`id` as `thread`, `Thread`.`comments`',
    ];
    $groupby = '`Ticket`.`id`';
} else {
    return 'Wrong action. You must use "ticket" or "comment".';
}

// Fields to select
$select = array_merge($select, [
    'Section' => $modx->getSelectColumns(Tickets2Section::class, 'Section', 'section.', ['content'], true),
    'User' => $modx->getSelectColumns(modUser::class, 'User', '', ['username']),
    'Profile' => $modx->getSelectColumns(modUserProfile::class, 'Profile', '', ['id'], true),
]);

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

$default = [
    'class' => $class,
    'where' => json_encode($where),
    'innerJoin' => json_encode($innerJoin),
    'leftJoin' => json_encode($leftJoin),
    'select' => json_encode($select),
    'sortby' => 'createdon',
    'sortdir' => 'DESC',
    'groupby' => $groupby,
    'return' => 'data',
    'nestedChunkPrefix' => 'tickets2_',
];

// Merge all properties and run!
$pdoFetch->setConfig(array_merge($default, $scriptProperties));
$pdoFetch->addTime('Query parameters are prepared.');
$rows = $pdoFetch->run();

// Processing rows
$output = [];
if (!empty($rows) && is_array($rows)) {
    foreach ($rows as $k => $row) {
        // Prepare row
        if ($class == Ticket::class) {
            $row['date_ago'] = $Tickets2->dateFormat($row['createdon']);
            $properties = is_string($row['properties'])
                ? json_decode($row['properties'], true)
                : $row['properties'];
            if (empty($properties['process_tags'])) {
                foreach ($row as $field => $value) {
                    $row[$field] = str_replace(
                        ['[', ']', '`', '{', '}'],
                        ['&#91;', '&#93;', '&#96;', '&#123;', '&#125;'],
                        $value
                    );
                }
            }
        } else {
            if (empty($row['createdby'])) {
                $row['fullname'] = $row['name'];
                $row['guest'] = 1;
            }
            $row['resource'] = $row['ticket.id'];
            $row = $Tickets2->prepareComment($row);
        }

        // Processing chunk
        $row['idx'] = $pdoFetch->idx++;
        $tpl = $pdoFetch->defineChunk($row);
        $output[] = !empty($tpl)
            ? $pdoFetch->getChunk($tpl, $row, $pdoFetch->config['fastMode'])
            : $pdoFetch->getChunk('', $row);
    }
    $pdoFetch->addTime('Returning processed chunks');
}
if (empty($outputSeparator)) {
    $outputSeparator = "\n";
}
$output = implode($outputSeparator, $output);

if (!empty($cacheKey)) {
    $modx->cacheManager->set('tickets2/latest.' . $cacheKey, $output, $cacheTime);
}

if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
    $output .= '<pre class="TicketLatestLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $output);
} else {
return $output;
}