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
            $policy = $modx->getObject(modAccessPolicy::class, array('name' => 'Tickets2UserPolicy'));
            if ($policy) {
                $template = $modx->getObject(modAccessPolicyTemplate::class, ['name' => 'Tickets2UserPolicyTemplate']);
                if ($template) {
                    $policy->set('template', $template->get('id'));
                    $policy->save();
                } else {
                    $modx->log(
                        xPDO::LOG_LEVEL_ERROR,
                        '[Tickets2] Could not find Tickets2UserPolicyTemplate Access Policy Template!'
                    );
                }
            } else {
                $modx->log(xPDO::LOG_LEVEL_ERROR, '[Tickets2] Could not find Tickets2UserPolicyTemplate Access Policy!');
            }
            break;
    }
}
return true;
