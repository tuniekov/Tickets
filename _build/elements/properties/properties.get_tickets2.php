<?php

$properties = array();

$tmp = array(
    'tpl' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.list.row',
    ),
    'limit' => array(
        'type' => 'numberfield',
        'value' => 10,
    ),
    'offset' => array(
        'type' => 'numberfield',
        'value' => 0,
    ),
    'depth' => array(
        'type' => 'numberfield',
        'value' => 10,
    ),
    'parents' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'resources' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'sortby' => array(
        'type' => 'textfield',
        'value' => 'createdon',
    ),
    'sortdir' => array(
        'type' => 'list',
        'options' => array(
            array('text' => 'ASC', 'value' => 'ASC'),
            array('text' => 'DESC', 'value' => 'DESC'),
        ),
        'value' => 'DESC',
    ),
    'includeContent' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'toPlaceholder' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'includeTVs' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'where' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'tvPrefix' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'outputSeparator' => array(
        'type' => 'textfield',
        'value' => "\n",
    ),
    'showLog' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'fastMode' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'showUnpublished' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'showDeleted' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'showHidden' => array(
        'type' => 'combo-boolean',
        'value' => true,
    ),
    'user' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'tplWrapper' => array(
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