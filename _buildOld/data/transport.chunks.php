<?php

$chunks = array();

$tmp = array(
    'tpl.Tickets2.form.create' => 'form_create',
    'tpl.Tickets2.form.update' => 'form_update',
    'tpl.Tickets2.form.preview' => 'form_preview',
    'tpl.Tickets2.form.files' => 'form_files',
    'tpl.Tickets2.form.file' => 'form_file',
    'tpl.Tickets2.form.image' => 'form_image',
    'tpl.Tickets2.comment.form.files' => 'comment_form_files',

    'tpl.Tickets2.ticket.latest' => 'ticket_latest',
    'tpl.Tickets2.ticket.email.bcc' => 'ticket_email_bcc',
    'tpl.Tickets2.ticket.email.subscription' => 'ticket_email_subscription',

    'tpl.Tickets2.comment.form' => 'comment_form',
    'tpl.Tickets2.comment.form.guest' => 'comment_form_guest',
    'tpl.Tickets2.comment.one.auth' => 'comment_one_auth',
    'tpl.Tickets2.comment.one.guest' => 'comment_one_guest',
    'tpl.Tickets2.comment.one.deleted' => 'comment_one_deleted',
    'tpl.Tickets2.comment.wrapper' => 'comment_wrapper',
    'tpl.Tickets2.comment.login' => 'comment_login',
    'tpl.Tickets2.comment.latest' => 'comment_latest',
    'tpl.Tickets2.comment.email.owner' => 'comment_email_owner',
    'tpl.Tickets2.comment.email.reply' => 'comment_email_reply',
    'tpl.Tickets2.comment.email.subscription' => 'comment_email_subscription',
    'tpl.Tickets2.comment.email.bcc' => 'comment_email_bcc',
    'tpl.Tickets2.comment.email.unpublished' => 'comment_email_unpublished',
    'tpl.Tickets2.comment.list.row' => 'comment_list_row',

    'tpl.Tickets2.list.row' => 'ticket_list_row',
    'tpl.Tickets2.sections.row' => 'ticket_sections_row',
    'tpl.Tickets2.sections.wrapper' => 'ticket_sections_wrapper',
    'tpl.Tickets2.meta' => 'ticket_meta',
    'tpl.Tickets2.meta.file' => 'ticket_meta_file',

    'tpl.Tickets2.author.subscribe' => 'ticket_author_subscribe',
    'tpl.Tickets2.author.email.subscription' => 'author_email_subscription',
);

/** @var modx $modx */
/** @var array $sources */
$BUILD_CHUNKS = array();
foreach ($tmp as $k => $v) {
    /** @var modChunk $chunk */
    $chunk = $modx->newObject('modChunk');
    $chunk->fromArray(array(
        'name' => $k,
        'description' => '',
        'snippet' => file_get_contents($sources['source_core'] . '/elements/chunks/chunk.' . $v . '.tpl'),
        'static' => BUILD_CHUNK_STATIC,
        'source' => 1,
        'static_file' => 'core/components/' . PKG_NAME_LOWER . '/elements/chunks/chunk.' . $v . '.tpl',
    ), '', true, true);
    $chunks[] = $chunk;

    $BUILD_CHUNKS[$k] = file_get_contents($sources['source_core'] . '/elements/chunks/chunk.' . $v . '.tpl');
}
ksort($BUILD_CHUNKS);

return $chunks;