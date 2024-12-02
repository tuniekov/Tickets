<?php

/** @var xPDO\Transport\xPDOTransport $transport */
/** @var array $options */

use MODX\Revolution\modSystemSetting;
use MODX\Revolution\Sources\modMediaSource;

if ($transport->xpdo) {
    $modx = $transport->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $tmp = explode('/', MODX_ASSETS_URL);
            $assets = $tmp[count($tmp) - 2];

            $properties = [
                'name' => 'Tickets2 Files',
                'description' => 'Default media source for files of tickets2',
                'class_key' => 'sources.modFileMediaSource',
                'properties' => [
                    'basePath' => [
                        'name' => 'basePath',
                        'desc' => 'prop_file.basePath_desc',
                        'type' => 'textfield',
                        'lexicon' => 'core:source',
                        'value' => $assets . '/images/tickets2/',
                    ],
                    'baseUrl' => [
                        'name' => 'baseUrl',
                        'desc' => 'prop_file.baseUrl_desc',
                        'type' => 'textfield',
                        'lexicon' => 'core:source',
                        'value' => 'assets/images/tickets2/',
                    ],
                    'imageExtensions' => [
                        'name' => 'imageExtensions',
                        'desc' => 'prop_file.imageExtensions_desc',
                        'type' => 'textfield',
                        'lexicon' => 'core:source',
                        'value' => 'jpg,jpeg,png,gif',
                    ],
                    'allowedFileTypes' => [
                        'name' => 'allowedFileTypes',
                        'desc' => 'prop_file.allowedFileTypes_desc',
                        'type' => 'textfield',
                        'lexicon' => 'core:source',
                        'value' => 'jpg,jpeg,png,gif',
                    ],
                    'thumbnailType' => [
                        'name' => 'thumbnailType',
                        'desc' => 'prop_file.thumbnailType_desc',
                        'type' => 'list',
                        'lexicon' => 'core:source',
                        'options' => [
                            ['text' => 'Png', 'value' => 'png'],
                            ['text' => 'Jpg', 'value' => 'jpg'],
                        ],
                        'value' => 'jpg',
                    ],
                    'thumbnails' => [
                        'name' => 'thumbnails',
                        'desc' => 'tickets2.source_thumbnails_desc',
                        'type' => 'textarea',
                        'lexicon' => 'tickets2:setting',
                        'value' => '{"thumb":{"w":120,"h":90,"q":90,"zc":"1","bg":"000000"}}',
                    ],
                    'maxUploadWidth' => [
                        'name' => 'maxUploadWidth',
                        'desc' => 'tickets2.source_maxUploadWidth_desc',
                        'type' => 'numberfield',
                        'lexicon' => 'tickets2:setting',
                        'value' => 1920,
                    ],
                    'maxUploadHeight' => [
                        'name' => 'maxUploadHeight',
                        'desc' => 'tickets2.source_maxUploadHeight_desc',
                        'type' => 'numberfield',
                        'lexicon' => 'tickets2:setting',
                        'value' => 1080,
                    ],
                    'maxUploadSize' => [
                        'name' => 'maxUploadSize',
                        'desc' => 'tickets2.source_maxUploadSize_desc',
                        'type' => 'numberfield',
                        'lexicon' => 'tickets2:setting',
                        'value' => 3145728,
                    ],
                    'imageNameType' => [
                        'name' => 'imageNameType',
                        'desc' => 'tickets2.source_imageNameType_desc',
                        'type' => 'list',
                        'lexicon' => 'tickets2:setting',
                        'options' => [
                            ['text' => 'Hash', 'value' => 'hash'],
                            ['text' => 'Friendly', 'value' => 'friendly'],
                        ],
                        'value' => 'hash',
                    ],
                ],
                'is_stream' => 1,
            ];

            /** @var modMediaSource $source */
            if (!$source = $modx->getObject(modMediaSource::class, ['name' => $properties['name']])) {
                $source = $modx->newObject(modMediaSource::class, $properties);
            } else {
                $default = $source->get('properties');
                foreach ($properties['properties'] as $k => $v) {
                    if (!array_key_exists($k, $default)) {
                        $default[$k] = $v;
                    }
                }
                $source->set('properties', $default);
            }
            $source->save();

            if ($setting = $modx->getObject(modSystemSetting::class, ['key' => 'tickets2.source_default'])) {
                if (!$setting->get('value')) {
                    $setting->set('value', $source->get('id'));
                    $setting->save();
                }
            }

            @mkdir(MODX_ASSETS_PATH . 'images/');
            @mkdir(MODX_ASSETS_PATH . 'images/tickets2/');
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

return true; 