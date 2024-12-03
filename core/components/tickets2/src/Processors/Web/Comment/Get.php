<?php

namespace Tickets2\Processors\Web\Comment;

use MODX\Revolution\Processors\Model\GetProcessor;
use Tickets2\Model\TicketComment;

class Get extends GetProcessor
{
    public $classKey = TicketComment::class;
    public $languageTopics = ['tickets2:default'];

    /**
     * @return array|string
     */
    public function cleanup()
    {
        $comment = $this->object->toArray();
        $comment['text'] = html_entity_decode($comment['text']);
        $comment['raw'] = html_entity_decode($comment['raw']);

        return $this->success('', $comment);
    }
} 