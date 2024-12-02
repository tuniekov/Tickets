<?php

$properties = array();

$tmp = array(
    'tpl' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.meta',
        'desc' => 'tickets2_prop_meta_tpl',
    ),
    'tplFile' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.meta.file',
        'desc' => 'tickets2_prop_meta_tplFile',
    ),
    /*
    'getSection' => array(
        'type' => 'combo-boolean',
        'value' => true,
    ),
    */
    'getUser' => array(
        'type' => 'combo-boolean',
        'value' => true,
    ),
    'getFiles' => array(
        'type' => 'combo-boolean',
        'value' => true,
    ),
    'unusedFiles' => array(
        'type' => 'combo-boolean',
        'value' => true,
    ),
    'thread' => array(
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