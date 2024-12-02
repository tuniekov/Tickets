<?php

namespace Tickets2\Processors\Mgr\Comment;

use MODX\Revolution\Processors\Model\GetProcessor;
use Tickets2\Model\TicketComment;

class Get extends GetProcessor
{
    public $objectType = 'TicketComment';
    public $classKey = TicketComment::class;
    public $languageTopics = ['tickets2:default'];

    /**
     * @return array
     */
    public function cleanup(): array
    {
        $comment = $this->object->toArray();
        $comment['createdon'] = $this->formatDate($comment['createdon']);
        $comment['editedon'] = $this->formatDate($comment['editedon']);
        $comment['deletedon'] = $this->formatDate($comment['deletedon']);
        $comment['text'] = !empty($comment['raw'])
            ? html_entity_decode($comment['raw'])
            : html_entity_decode($comment['text']);

        return $this->success('', $comment);
    }

    /**
     * @param string $date
     *
     * @return string
     */
    protected function formatDate(string $date = ''): string
    {
        if (empty($date) || $date == '0000-00-00 00:00:00') {
            return $this->modx->lexicon('no');
        }

        return strftime($this->modx->getOption('tickets2.date_format'), strtotime($date));
    }
} 