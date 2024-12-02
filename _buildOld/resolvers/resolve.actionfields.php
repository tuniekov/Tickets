<?php

$actionFields = array(
    array(
        'name' => 'tickets2-box-publishing-information',
        'tab' => 'modx-resource-main-right',
        'fields' => array(
            'publishedon',
            'pub_date',
            'unpub_date',
            'template',
            'modx-resource-createdby',
            'tickets2-combo-section',
            'alias',
        ),
    ),
    array(
        'name' => 'tickets2-box-options',
        'tab' => 'modx-resource-main-right',
        'fields' => array(
            'searchable',
            'properties[disable_jevix]',
            'cacheable',
            'properties[process_tags]',
            'published',
            'private',
            'privateweb',
            'richtext',
            'hidemenu',
            'isfolder',
            'show_in_tree',
        ),
    ),
    array(
        'name' => 'modx-tickets2-comments',
        'tab' => '',
        'fields' => array(),
    ),
    array(
        'name' => 'modx-tickets2-subscribes',
        'tab' => '',
        'fields' => array(),
    ),
);

$resourceActions = array('resource/create', 'resource/update');

/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            /** @var modActionField $action */
            if ($modx->getCount('modActionField', array('name' => 'publishedon', 'other' => 'tickets2')) > 1) {
                $modx->removeCollection('modActionField', array('other' => 'tickets2'));
            }

            foreach ($resourceActions as $actionId) {
                $c = $modx->newQuery('modActionField', array('type' => 'tab', 'action' => $actionId));
                $c->select('max(`rank`)');
                $tabIdx = 0;
                if ($c->prepare() && $c->stmt->execute()) {
                    $tabIdx = $c->stmt->fetchColumn();
                    $tabIdx += 1;
                }

                foreach ($actionFields as $tab) {
                    /** @var modActionField $tabObj */
                    if (!$tabObj = $modx->getObject('modActionField',
                        array('action' => $actionId, 'name' => $tab['name'], 'other' => 'tickets2'))
                    ) {
                        $tabObj = $modx->newObject('modActionField');
                    }
                    $tabObj->fromArray(array_merge($tab, array(
                        'action' => $actionId,
                        'form' => 'modx-panel-resource',
                        'type' => 'tab',
                        'other' => 'tickets2',
                        'rank' => $tabIdx,
                    )), '', true, true);
                    $success = $tabObj->save();
                    /*if ($success) {
                        $modx->log(xPDO::LOG_LEVEL_INFO,'[Tickets2] Tab ' . $tab['name'] . ' added!');
                    } else {
                        $modx->log(xPDO::LOG_LEVEL_ERROR,'[Tickets2] Could not add Tab ' . $tab['name'] . '!');
                    }*/

                    $tabIdx++;
                    $idx = 0;
                    foreach ($tab['fields'] as $field) {
                        if (!$fieldObj = $modx->getObject('modActionField',
                            array('action' => $actionId, 'name' => $field, 'tab' => $tab['name'], 'other' => 'tickets2'))
                        ) {
                            $fieldObj = $modx->newObject('modActionField');
                        }
                        $fieldObj->fromArray(array(
                            'action' => $actionId,
                            'name' => $field,
                            'tab' => $tab['name'],
                            'form' => 'modx-panel-resource',
                            'type' => 'field',
                            'other' => 'tickets2',
                            'rank' => $idx,
                        ), '', true, true);
                        $success = $fieldObj->save();
                        /*if ($success) {
                            $modx->log(xPDO::LOG_LEVEL_INFO,'[Tickets2] Action field ' . $field . ' added!');
                        } else {
                            $modx->log(xPDO::LOG_LEVEL_ERROR,'[Tickets2] Could not add Action Field ' . $field . '!');
                        }*/
                        $idx++;

                    }
                }
            }
            break;
        case xPDOTransport::ACTION_UNINSTALL:
            $modx->removeCollection('modActionField', array('other' => 'tickets2'));
            break;
    }
}

return true;
