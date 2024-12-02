<?php
use MODX\Revolution\modX;
use Tickets2\Model\{Ticket, TicketAuthor};
use Tickets2\Tickets2;

/** @var modX $modx */
/** @var array $scriptProperties */

/** @var Tickets2 $Tickets2 */
$Tickets2 = $modx->getService('tickets2', Tickets2::class, 
    $modx->getOption('tickets2.core_path', null, $modx->getOption('core_path') . 'components/tickets2/') . 'model/tickets2/',
    $scriptProperties
);

if (!$Tickets2->authenticated || empty($scriptProperties['createdby'])) {
    return '';
}

if (!empty($scriptProperties['Tickets2Init'])) {
    $Tickets2->initialize($modx->context->key, $scriptProperties);
}

if ($profile = $modx->getObject(TicketAuthor::class, ['id' => $scriptProperties['createdby']])) {
    $properties = $profile->get('properties');
    if (!empty($properties['subscribers'])) {
        $found = array_search($modx->user->id, $properties['subscribers']);
        $subscribed = ($found === false) ? 0 : 1;
    }
}

$tpl = $modx->getOption('tpl', $scriptProperties, 'tpl.Tickets2.author.subscribe');
$data = [
    'author_id' => $scriptProperties['createdby'],
    'subscribed' => $subscribed
];
$output = $Tickets2->getChunk($tpl, $data);

// Return output
if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $output);
} else {
    return $output;
}