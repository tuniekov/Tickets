<?php

namespace Tickets2\Processors\Mgr\Author;

use MODX\Revolution\modUser;
use MODX\Revolution\Processors\Processor;
use Tickets2\Model\TicketAuthor;
use Tickets2\Model\TicketAuthorAction;
use xPDO\Om\xPDOQuery;

class Rebuild extends Processor
{
    /**
     * @return array|string
     */
    public function process(): array
    {
        $time = time();
        $time_limit = @ini_get('max_execution_time') - 20;
        if ($time_limit <= 5) {
            $time_limit = 5;
        }

        $start = $this->getProperty('start', 0);
        $c = $this->modx->newQuery(modUser::class);
        if ($start == 0) {
            $this->cleanTables();
        } else {
            $c->limit(1000000, $start);
        }
        $users = $this->modx->getIterator(modUser::class, $c);
        /** @var modUser $user */
        foreach ($users as $user) {
            /** @var TicketAuthor $profile */
            if (!$profile = $user->getOne('AuthorProfile')) {
                $profile = $this->modx->newObject(TicketAuthor::class);
                $user->addOne($profile);
            }
            $profile->refreshActions(false);
            $start++;
            if ((time() - $time) >= $time_limit) {
                return $this->cleanup($start);
            }
        }

        return $this->cleanup($start);
    }

    /**
     * @param int $processed
     *
     * @return array|string
     */
    public function cleanup(int $processed = 0): array
    {
        return $this->success('', [
            'total' => $this->modx->getCount(modUser::class),
            'processed' => $processed,
        ]);
    }

    /**
     * Clean tables before rebuild
     */
    protected function cleanTables(): void
    {
        /** @var xPDOQuery $c */
        $c = $this->modx->newQuery(TicketAuthor::class);
        $c->command('UPDATE');
        $c->set([
            'tickets2' => 0,
            'comments' => 0,
            'views' => 0,
            'stars_tickets2' => 0,
            'stars_comments' => 0,
            'votes_tickets2' => 0,
            'votes_comments' => 0,
            'votes_tickets2_up' => 0,
            'votes_tickets2_down' => 0,
            'votes_comments_up' => 0,
            'votes_comments_down' => 0,
        ]);
        $c->prepare();
        $c->stmt->execute();

        $this->modx->removeCollection(TicketAuthorAction::class, []);
    }
} 