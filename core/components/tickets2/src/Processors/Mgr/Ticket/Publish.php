<?php

namespace Tickets2\Processors\Mgr\Ticket;

use MODX\Revolution\Processors\Resource\Publish as ResourcePublish;
use Tickets2\Model\Ticket;

class Publish extends ResourcePublish
{
    public $classKey = Ticket::class;
    public $permission = 'ticket_publish';

    /**
     * Fire after publish event
     */
    public function fireAfterPublish(): void
    {
        parent::fireAfterPublish();
        $this->sendTicketMails();
    }

    /**
     * Call method for notify users about publish ticket
     */
    protected function sendTicketMails(): void
    {
        /** @var \Tickets2 $Tickets2 */
        if ($Tickets2 = $this->modx->getService('Tickets2')) {
            $Tickets2->config['tplTicketEmailBcc'] = 'tpl.Tickets2.ticket.email.bcc';
            $Tickets2->config['tplTicketEmailSubscription'] = 'tpl.Tickets2.ticket.email.subscription';
            $Tickets2->config['tplAuthorEmailSubscription'] = 'tpl.Tickets2.author.email.subscription';
            $Tickets2->sendTicketMails($this->resource->toArray(), true);
        }
    }
} 