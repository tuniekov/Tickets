<?php

$properties = array();

$tmp = array(
    'thread' => array(
        'name' => 'thread',
        'type' => 'textfield',
        'value' => '',
    ),
    'fastMode' => array(
        'type' => 'combo-boolean',
        'value' => true,
    ),/*
	'dateFormat' => array(
		'type' => 'textfield'
		'value' => 'd F Y H:i'
	),*/
    'gravatarIcon' => array(
        'type' => 'textfield',
        'value' => 'mm',
    ),
    'gravatarSize' => array(
        'type' => 'numberfield',
        'value' => '24',
    ),
    'gravatarUrl' => array(
        'type' => 'textfield',
        'value' => 'https://www.gravatar.com/avatar/',
    ),
    'tplCommentForm' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.comment.form',
    ),
    'tplCommentFormGuest' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.comment.form.guest',
    ),
    'tplCommentAuth' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.comment.one.auth',
    ),
    'tplCommentGuest' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.comment.one.guest',
    ),
    'tplCommentDeleted' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.comment.one.deleted',
    ),
    'tplComments' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.comment.wrapper',
    ),
    'tplLoginToComment' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.comment.login',
    ),
    'tplCommentEmailOwner' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.comment.email.owner',
    ),
    'tplCommentEmailReply' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.comment.email.reply',
    ),
    'tplCommentEmailSubscription' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.comment.email.subscription',
    ),
    'tplCommentEmailBcc' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.comment.email.bcc',
    ),
    'tplCommentEmailUnpublished' => array(
        'type' => 'textfield',
        'value' => 'tpl.Tickets2.comment.email.unpublished',
    ),
    'autoPublish' => array(
        'type' => 'combo-boolean',
        'value' => true,
    ),
    'autoPublishGuest' => array(
        'type' => 'combo-boolean',
        'value' => true,
    ),
    'formBefore' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'depth' => array(
        'type' => 'numberfield',
        'desc' => 'tickets2_prop_commentsDepth',
        'value' => 0,
    ),
    'allowGuest' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'allowGuestEdit' => array(
        'type' => 'combo-boolean',
        'value' => true,
    ),
    'allowGuestEmails' => array(
        'type' => 'combo-boolean',
        'value' => false,
    ),
    'enableCaptcha' => array(
        'type' => 'combo-boolean',
        'value' => true,
    ),
    'minCaptcha' => array(
        'type' => 'numberfield',
        'value' => 1,
    ),
    'maxCaptcha' => array(
        'type' => 'numberfield',
        'value' => 10,
    ),
    'requiredFields' => array(
        'type' => 'textfield',
        'value' => 'name,email',
    ),
    'threadUrl' => array(
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
