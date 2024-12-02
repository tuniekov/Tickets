<?php

/** @var xPDO\Transport\xPDOTransport $transport */
/** @var array $options */

use MODX\Revolution\modAccessPolicy;
use MODX\Revolution\modAccessPolicyTemplate;

if ($transport->xpdo) {
    $modx = $transport->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            // Assign policy to template
            if ($policy = $modx->getObject(modAccessPolicy::class, ['name' => 'TicketUserPolicy'])) {
                if ($template = $modx->getObject(modAccessPolicyTemplate::class, ['name' => 'Tickets2UserPolicyTemplate'])) {
                    $policy->set('template', $template->get('id'));
                    $policy->save();
                } else {
                    $modx->log(xPDO::LOG_LEVEL_ERROR, '[Tickets2] Could not find Tickets2UserPolicyTemplate Access Policy Template!');
                }
            } else {
                $modx->log(xPDO::LOG_LEVEL_ERROR, '[Tickets2] Could not find TicketUserPolicy Access Policy!');
            }

            if ($policy = $modx->getObject(modAccessPolicy::class, ['name' => 'TicketVipPolicy'])) {
                if ($template = $modx->getObject(modAccessPolicyTemplate::class, ['name' => 'Tickets2UserPolicyTemplate'])) {
                    $policy->set('template', $template->get('id'));
                    $policy->save();
                } else {
                    $modx->log(xPDO::LOG_LEVEL_ERROR, '[Tickets2] Could not find Tickets2UserPolicyTemplate Access Policy Template!');
                }
            } else {
                $modx->log(xPDO::LOG_LEVEL_ERROR, '[Tickets2] Could not find TicketVipPolicy Access Policy!');
            }

            if ($policy = $modx->getObject(modAccessPolicy::class, ['name' => 'TicketSectionPolicy'])) {
                if ($template = $modx->getObject(modAccessPolicyTemplate::class, ['name' => 'Tickets2SectionPolicyTemplate'])) {
                    $policy->set('template', $template->get('id'));
                    $policy->save();
                } else {
                    $modx->log(xPDO::LOG_LEVEL_ERROR, '[Tickets2] Could not find Tickets2SectionPolicyTemplate Access Policy Template!');
                }
            } else {
                $modx->log(xPDO::LOG_LEVEL_ERROR, '[Tickets2] Could not find TicketSectionPolicy Access Policy!');
            }
            break;
    }
}

return true; 