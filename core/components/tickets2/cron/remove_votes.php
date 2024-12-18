<?php

define('MODX_API_MODE', true);

/** @noinspection PhpIncludeInspection */
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/index.php';

use Tickets2\Model\Ticket;
use Tickets2\Model\TicketComment;
use Tickets2\Model\Tickets2Section;
use Tickets2\Model\TicketVote;

/** @var modX $modx */
$modx->getService('error', 'error.modError');
$modx->getRequest();
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');
$modx->error->message = null;

$time = time();

$sections = $modx->getIterator(Tickets2Section::class, ['class_key' => Tickets2Section::class]);
/** @var Tickets2Section $section */
foreach ($sections as $section) {
    $ratings = $section->getProperties('ratings');
    if (isset($ratings['days_ticket_vote']) && $ratings['days_ticket_vote'] != '') {
        $max = (float)$ratings['days_ticket_vote'] * 86400;
        $sql = "
            DELETE Vote FROM {$modx->getTableName(TicketVote::class)} Vote
            JOIN {$modx->getTableName(Ticket::class)} Ticket
            ON (Ticket.id = Vote.id AND Vote.class = 'Ticket')
            WHERE Ticket.parent = {$section->id} AND UNIX_TIMESTAMP(Vote.createdon) > (Ticket.createdon + {$max});
        ";
        $c = $modx->prepare($sql);
        $c->execute();
    }
    if (isset($ratings['days_comment_vote']) && $ratings['days_comment_vote'] != '') {
        $max = (float)$ratings['days_comment_vote'] * 86400;
        $sql = "
            DELETE Vote FROM {$modx->getTableName(TicketVote::class)} Vote
            JOIN {$modx->getTableName(TicketComment::class)} Comment
            ON (Comment.id = Vote.id AND Vote.class = 'TicketComment')
            JOIN {$modx->getTableName('TicketThread')} Thread
            ON (Thread.id = Comment.thread)
            JOIN {$modx->getTableName(Ticket::class)} Ticket
            ON (Ticket.id = Thread.resource)
            WHERE Ticket.parent = {$section->id} AND UNIX_TIMESTAMP(Vote.createdon) > (UNIX_TIMESTAMP(Comment.createdon) + {$max});
        ";
        $c = $modx->prepare($sql);
        $c->execute();
    }
}

$comments = $modx->getIterator(TicketComment::class);
/** @var TicketComment $comment */
foreach ($comments as $comment) {
    $comment->updateRating();
}

echo "Done in " . (time() - $time) . " sec.\n\n";