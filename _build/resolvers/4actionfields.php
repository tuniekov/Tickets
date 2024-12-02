<?php

/** @var xPDO\Transport\xPDOTransport $transport */
/** @var array $options */

use MODX\Revolution\modActionField;

$actionFields = [
    [
        'name' => 'tickets2-box-publishing-information',
        'tab' => 'modx-resource-main-right',
        'fields' => [
            'publishedon',
            'pub_date',
            'unpub_date',
            'template',
            'modx-resource-createdby',
            'tickets2-combo-section',
            'alias',
        ],
    ],
    [
        'name' => 'tickets2-box-options',
        'tab' => 'modx-resource-main-right',
        'fields' => [
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
        ],
    ],
    [
        'name' => 'modx-tickets2-comments',
        'tab' => '',
        'fields' => [],
    ],
    [
        'name' => 'modx-tickets2-subscribes',
        'tab' => '',
        'fields' => [],
    ],
];

$resourceActions = ['resource/create', 'resource/update'];

if ($transport->xpdo) {
    $modx = $transport->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            /** @var modActionField $action */
            if ($modx->getCount(modActionField::class, ['name' => 'publishedon', 'other' => 'tickets2']) > 1) {
                $modx->removeCollection(modActionField::class, ['other' => 'tickets2']);
            }

            foreach ($resourceActions as $actionId) {
                $c = $modx->newQuery(modActionField::class, ['type' => 'tab', 'action' => $actionId]);
                $c->select('max(`rank`)');
                $tabIdx = 0;
                if ($c->prepare() && $c->stmt->execute()) {
                    $tabIdx = $c->stmt->fetchColumn();
                    $tabIdx += 1;
                }

                foreach ($actionFields as $tab) {
                    /** @var modActionField $tabObj */
                    if (!$tabObj = $modx->getObject(modActionField::class, [
                        'action' => $actionId,
                        'name' => $tab['name'],
                        'other' => 'tickets2'
                    ])) {
                        $tabObj = $modx->newObject(modActionField::class);
                    }
                    $tabObj->fromArray(array_merge($tab, [
                        'action' => $actionId,
                        'form' => 'modx-panel-resource',
                        'type' => 'tab',
                        'other' => 'tickets2',
                        'rank' => $tabIdx,
                    ]), '', true, true);
                    $tabObj->save();

                    $tabIdx++;
                    $idx = 0;
                    foreach ($tab['fields'] as $field) {
                        if (!$fieldObj = $modx->getObject(modActionField::class, [
                            'action' => $actionId,
                            'name' => $field,
                            'tab' => $tab['name'],
                            'other' => 'tickets2'
                        ])) {
                            $fieldObj = $modx->newObject(modActionField::class);
                        }
                        $fieldObj->fromArray([
                            'action' => $actionId,
                            'name' => $field,
                            'tab' => $tab['name'],
                            'form' => 'modx-panel-resource',
                            'type' => 'field',
                            'other' => 'tickets2',
                            'rank' => $idx,
                        ], '', true, true);
                        $fieldObj->save();
                        $idx++;
                    }
                }
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            $modx->removeCollection(modActionField::class, ['other' => 'tickets2']);
            break;
    }
}

return true; 