<?php

$settings = array();

$tmp = array(
    'mgr_tree_icon_tickets2section' => array(
        'xtype' => 'textfield',
        'value' => 'icon icon-comments-o',
        'area' => 'tickets2.main',
        'key' => 'mgr_tree_icon_tickets2section',
    ),
    'mgr_tree_icon_ticket' => array(
        'xtype' => 'textfield',
        'value' => 'icon icon-comment-o',
        'area' => 'tickets2.main',
        'key' => 'mgr_tree_icon_ticket',
    ),
    'date_format' => array(
        'xtype' => 'textfield',
        'value' => '%d.%m.%y <small>%H:%M</small>',
        'area' => 'tickets2.main',
    ),
    'enable_editor' => array(
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'tickets2.main',
    ),
    'frontend_css' => array(
        'value' => '[[+cssUrl]]web/default.css',
        'xtype' => 'textfield',
        'area' => 'tickets2.main',
    ),
    'frontend_js' => array(
        'value' => '[[+jsUrl]]web/default.js',
        'xtype' => 'textfield',
        'area' => 'tickets2.main',
    ),
    'editor_config.ticket' => array(
        'xtype' => 'textarea',
        'value' => '{onTab: {keepDefault:false, replaceWith:"	"},
        markupSet: [
            {name:"Bold", className: "btn-bold", key:"B", openWith:"<b>", closeWith:"</b>" },
            {name:"Italic", className: "btn-italic", key:"I", openWith:"<i>", closeWith:"</i>"  },
            {name:"Underline", className: "btn-underline", key:"U", openWith:"<u>", closeWith:"</u>" },
            {name:"Stroke through", className: "btn-stroke", key:"S", openWith:"<s>", closeWith:"</s>" },
            {separator:"---------------" },
            {name:"Bulleted List", className: "btn-bulleted", openWith:"	<li>", closeWith:"</li>", multiline:true, openBlockWith:"<ul>\n", closeBlockWith:"\n</ul>"},
            {name:"Numeric List", className: "btn-numeric", openWith:"	<li>", closeWith:"</li>", multiline:true, openBlockWith:"<ol>\n", closeBlockWith:"\n</ol>"},
            {separator:"---------------" },
            {name:"Quote", className: "btn-quote", openWith:"<blockquote>", closeWith:"</blockquote>"},
            {name:"Code", className: "btn-code", openWith:"<code>", closeWith:"</code>"},
            {name:"Link", className: "btn-link", openWith:"<a href=\"[![Link:!:http://]!]\">", closeWith:"</a>" },
            {name:"Picture", className: "btn-picture", replaceWith:"<img src=\"[![Source:!:http://]!]\" />" },
            {separator:"---------------" },
            {name:"Cut", className: "btn-cut", openWith:"<cut/>" }
        ]}',
        'area' => 'tickets2.ticket',
    ),
    'editor_config.comment' => array(
        'xtype' => 'textarea',
        'value' => '{onTab: {keepDefault:false, replaceWith:"	"},
        markupSet: [
            {name:"Bold", className: "btn-bold", key:"B", openWith:"<b>", closeWith:"</b>" },
            {name:"Italic", className: "btn-italic", key:"I", openWith:"<i>", closeWith:"</i>"  },
            {name:"Underline", className: "btn-underline", key:"U", openWith:"<u>", closeWith:"</u>" },
            {name:"Stroke through", className: "btn-stroke", key:"S", openWith:"<s>", closeWith:"</s>" },
            {separator:"---------------" },
            {name:"Quote", className: "btn-quote", openWith:"<blockquote>", closeWith:"</blockquote>"},
            {name:"Code", className: "btn-code", openWith:"<code>", closeWith:"</code>"},
            {name:"Link", className: "btn-link", openWith:"<a href=\"[![Link:!:http://]!]\">", closeWith:"</a>" },
            {name:"Picture", className: "btn-picture", replaceWith:"<img src=\"[![Source:!:http://]!]\" />" }
        ]}',
        'area' => 'tickets2.comment',
    ),
    'default_template' => array(
        'xtype' => 'modx-combo-template',
        'value' => '',
        'area' => 'tickets2.ticket',
    ),
    'snippet_prepare_comment' => array(
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'tickets2.comment',
    ),
    'comment_edit_time' => array(
        'xtype' => 'numberfield',
        'value' => 600,
        'area' => 'tickets2.comment',
    ),
    'clear_cache_on_comment_save' => array(
        'xtype' => 'combo-boolean',
        'value' => false,
        'area' => 'tickets2.comment',
    ),
    'private_ticket_page' => array(
        'xtype' => 'numberfield',
        'value' => 0,
        'area' => 'tickets2.ticket',
    ),
    'unpublished_ticket_page' => array(
        'xtype' => 'numberfield',
        'value' => 0,
        'area' => 'tickets2.ticket',
    ),
    'ticket_max_cut' => array(
        'xtype' => 'numberfield',
        'value' => 1000,
        'area' => 'tickets2.ticket',
    ),

    'mail_from' => array(
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'tickets2.mail',
    ),
    'mail_from_name' => array(
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'tickets2.mail',
    ),
    'mail_queue' => array(
        'xtype' => 'combo-boolean',
        'value' => false,
        'area' => 'tickets2.mail',
    ),
    'mail_bcc' => array(
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'tickets2.mail',
    ),
    'mail_bcc_level' => array(
        'xtype' => 'numberfield',
        'value' => 1,
        'area' => 'tickets2.mail',
    ),
    'section_content_default' => array(
        'value' => '',
        'xtype' => 'textarea',
        'area' => 'tickets2.section',
    ),

    'source_default' => array(
        'value' => 0,
        'xtype' => 'modx-combo-source',
        'area' => 'tickets2.main',
    ),

    'count_guests' => array(
        'xtype' => 'combo-boolean',
        'value' => false,
        'area' => 'tickets2.ticket',
    ),

    'max_files_upload' => array(
        'xtype' => 'numberfield',
        'value' => 0,
        'area' => 'tickets2.ticket',
    ),
);

/** @var modx $modx */
foreach ($tmp as $k => $v) {
    /** @var modSystemSetting $setting */
    $setting = $modx->newObject('modSystemSetting');
    $setting->fromArray(array_merge(
        array(
            'key' => PKG_NAME_LOWER . '.' . $k,
            'namespace' => PKG_NAME_LOWER,
        ), $v
    ), '', true, true);
    $settings[] = $setting;
}

return $settings;