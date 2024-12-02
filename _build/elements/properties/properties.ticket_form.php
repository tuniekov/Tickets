<?php

$properties = array();

$tmp = array(
    'tplFormCreate' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.form.create',
    ),
    'tplFormUpdate' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.form.update',
    ),
    'tplPreview' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.form.preview',
    ),
    'tplSectionRow' => array(
        'type' => 'textfield',
        'value' => '@INLINE <option value="[[+id]]" [[+selected]]>[[+pagetitle]]</option>',
    ),
    'tplTicketEmailBcc' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.ticket.email.bcc',
    ),
    'tplTicketEmailSubscription' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.ticket.email.subscription',
    ),
    'allowedFields' => array(
        'type' => 'textfield',
        'value' => 'parent,pagetitle,content',
    ),
    'requiredFields' => array(
        'type' => 'textfield',
        'value' => 'parent,pagetitle,content',
    ),
    'bypassFields' => array(
        'type' => 'textfield',
        'value' => '',
    ),
    'redirectUnpublished' => array(
        'type' => 'numberfield',
        'value' => 0,
    ),

    'parents' => array(
        'type' => 'textfield',
        'value' => '',
        'desc' => 'tickets2_prop_sections_parents',
    ),
    'resources' => array(
        'type' => 'textfield',
        'value' => '',
        'desc' => 'tickets2_prop_sections_resources',
    ),
    'sortby' => array(
        'type' => 'textfield',
        'value' => 'pagetitle',
        'desc' => 'tickets2_prop_sections_sortby',
    ),
    'sortdir' => array(
        'type' => 'list',
        'options' => array(
            array('text' => 'ASC', 'value' => 'ASC'),
            array('text' => 'DESC', 'value' => 'DESC'),
        ),
        'value' => 'ASC',
        'desc' => 'tickets2_prop_sections_sortdir',
    ),
    'context' => array(
        'type' => 'textfield',
        'value' => '',
        'desc' => 'tickets2_prop_sections_context',
    ),

    'allowFiles' => array(
        'type' => 'combo-boolean',
        'value' => true,
    ),
    'source' => array(
        'type' => 'numberfield',
        'value' => 0,
    ),
    'tplFiles' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.form.files',
    ),
    'tplFile' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.form.file',
    ),
    'tplImage' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.form.image',
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