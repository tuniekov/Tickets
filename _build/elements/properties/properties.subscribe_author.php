<?php

$properties = array();

$tmp = array(
    'tpl' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.author.subscribe',
    ),
    'Tickets2Init' => array(
        'type' => 'textfield',
        'value' => '0',
    ),
    'createdby' => array(
        'type' => 'textfield',
        'value' => '0',
    ),
);

foreach ($tmp as $k => $v) {
    $properties[$k] = array_merge(array(
        'name' => $k,
        'desc' => 'tickets2_prop_' . $k,
        'lexicon' => 'tickets2:properties',
    ), $v);
}

return $properties;