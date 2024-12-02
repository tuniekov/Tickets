<?php
use MODX\Revolution\modX;
use Tickets2\Model\{Ticket, TicketTotal, Tickets2Section};
use Tickets2\Tickets2;

/** @var modX $modx */
/** @var array $scriptProperties */

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

if (isset($parents) && $parents === '') {
    $scriptProperties['parents'] = $modx->resource->id;
}

$class = Tickets2Section::class;
$where = ['class_key' => $class];

// Add custom parameters
foreach (['where'] as $v) {
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

// Joining tables
$leftJoin = [
    'Total' => ['class' => TicketTotal::class],
];

// Fields to select
$select = [
    'Tickets2Section' => !empty($includeContent)
        ? $modx->getSelectColumns($class, $class)
        : $modx->getSelectColumns($class, $class, '', ['content'], true),
    'Total' => 'tickets2, comments, views, stars, rating, rating_plus, rating_minus',
];

$default = [
    'class' => $class,
    'where' => json_encode($where),
    'leftJoin' => json_encode($leftJoin),
    'select' => json_encode($select),
    'groupby' => $class . '.id',
    'sortby' => 'views',
    'sortdir' => 'DESC',
    'return' => !empty($returnIds)
        ? 'ids'
        : 'data',
    'nestedChunkPrefix' => 'tickets2_',
];

// Merge all properties and run!
$pdoFetch->setConfig(array_merge($default, $scriptProperties));
$pdoFetch->addTime('Query parameters are prepared.');
$rows = $pdoFetch->run();

if (!empty($returnIds)) {
    return $rows;
}

// Processing rows
$output = [];
if (!empty($rows) && is_array($rows)) {
    foreach ($rows as $k => $row) {
        $row['date_ago'] = $Tickets2->dateFormat($row['createdon']);
        $row['idx'] = $pdoFetch->idx++;

        $tpl = $pdoFetch->defineChunk($row);
        $output[] = empty($tpl)
            ? '<pre>' . $pdoFetch->getChunk('', $row) . '</pre>'
            : $pdoFetch->getChunk($tpl, $row, $pdoFetch->config['fastMode']);
    }
}
$pdoFetch->addTime('Returning processed chunks');
if (empty($outputSeparator)) {
    $outputSeparator = "\n";
}
$output = implode($outputSeparator, $output);

$log = '';
if ($modx->user->hasSessionContext('mgr') && !empty($showLog)) {
    $log .= '<pre class="getSectionsLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

// Return output
if (!empty($toSeparatePlaceholders)) {
    $output['log'] = $log;
    $modx->setPlaceholders($output, $toSeparatePlaceholders);
} else {
    $output .= $log;

    if (!empty($tplWrapper) && (!empty($wrapIfEmpty) || !empty($output))) {
        $output = $pdoFetch->getChunk($tplWrapper, ['output' => $output], $pdoFetch->config['fastMode']);
    }

    if (!empty($toPlaceholder)) {
        $modx->setPlaceholder($toPlaceholder, $output);
    } else {
        return $output;
    }
}