<?php

/** @var xPDO\Transport\xPDOTransport $transport */
/** @var array $options */

use Tickets2\Model\Ticket;
use Tickets2\Model\TicketThread;
use Tickets2\Model\TicketVote;

if ($transport->xpdo) {
    $modx = $transport->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            // Update comments count
            $threads = $modx->getIterator(TicketThread::class, ['comments' => 0]);
            /** @var TicketThread $thread */
            foreach ($threads as $thread) {
                $thread->updateCommentsCount();
            }

            // Update owners of votes entries
            $tmp = [];
            $q = $modx->newQuery(TicketVote::class, ['owner' => 0]);
            $q->select('class,id');
            if ($q->prepare() && $q->stmt->execute()) {
                while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (empty($tmp[$row['class']])) {
                        $tmp[$row['class']] = [$row['id']];
                    } else {
                        $tmp[$row['class']][] = $row['id'];
                    }
                }
                if (!empty($tmp)) {
                    foreach ($tmp as $k => $v) {
                        $q = $modx->newQuery($k, ['id:IN' => $v]);
                        $q->select('id,createdby');

                        $table = $modx->getTableName(TicketVote::class);
                        $sql = "";
                        if ($q->prepare() && $q->stmt->execute()) {
                            while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                                $sql .= "UPDATE {$table} SET `owner` = {$row['createdby']} WHERE `id` = {$row['id']} AND `class` = '{$k}';\n";
                            }
                        }
                        $modx->exec($sql);
                    }
                }
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

return true; 