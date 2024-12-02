<?php

$properties = array();

$tmp = array(
    'class' => array(
        'type' => 'textfield',
        'value' => 'Ticket',
    ),
    'tpl' => array(
        'type' => 'textfield',
        'value' => '',
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