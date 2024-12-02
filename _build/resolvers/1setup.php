<?php

use MODX\Revolution\modPropertySet;
use MODX\Revolution\modSnippet;
use MODX\Revolution\Transport\modTransportPackage;
use MODX\Revolution\Transport\modTransportProvider;

/** @var xPDO\Transport\xPDOTransport $transport */
/** @var array $options */

if (!$transport->xpdo || !($transport instanceof xPDOTransport)) {
    return false;
}

$modx = $transport->xpdo;
$packages = [
    'pdoTools' => [
        'version' => '3.0.0-pl',
        'service_url' => 'modstore.pro',
    ],
    'Jevix' => [
        'version' => '1.2.0-pl',
        'service_url' => 'modstore.pro',
    ],
];

$downloadPackage = function ($src, $dst) {
    if (ini_get('allow_url_fopen')) {
        $file = @file_get_contents($src);
    } else {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $src);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 180);
            $safeMode = @ini_get('safe_mode');
            $openBasedir = @ini_get('open_basedir');
            if (empty($safeMode) && empty($openBasedir)) {
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            }

            $file = curl_exec($ch);
            curl_close($ch);
        } else {
            return false;
        }
    }
    file_put_contents($dst, $file);

    return file_exists($dst);
};

$installPackage = function ($packageName, $options = []) use ($modx, $downloadPackage) {
    /** @var modTransportProvider $provider */
    if (!empty($options['service_url'])) {
        $provider = $modx->getObject(modTransportProvider::class, [
            'service_url:LIKE' => '%' . $options['service_url'] . '%',
        ]);
    }
    if (empty($provider)) {
        $provider = $modx->getObject(modTransportProvider::class, 1);
    }
    $modx->getVersionData();
    $productVersion = $modx->version['code_name'] . '-' . $modx->version['full_version'];

    $response = $provider->request('package', 'GET', [
        'supports' => $productVersion,
        'query' => $packageName,
    ]);

    if (empty($response)) {
        return [
            'success' => 0,
            'message' => "Could not find <b>{$packageName}</b> in MODX repository",
        ];
    }

    $foundPackages = simplexml_load_string($response->getBody()->getContents());
    foreach ($foundPackages as $foundPackage) {
        /** @var modTransportPackage $foundPackage */
        /** @noinspection PhpUndefinedFieldInspection */
        if ((string)$foundPackage->name === $packageName) {
            $sig = explode('-', (string)$foundPackage->signature);
            $versionSignature = explode('.', $sig[1]);
            /** @var modTransportPackage $package */
            $package = $provider->transfer((string)$foundPackage->signature);
            if ($package && $package->install()) {
                return [
                    'success' => 1,
                    'message' => "<b>{$packageName}</b> was successfully installed",
                ];
            }
            return [
                'success' => 0,
                'message' => "Could not save package <b>{$packageName}</b>",
            ];
        }
    }

    return true;
};

$success = false;
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        foreach ($packages as $name => $data) {
            if (!is_array($data)) {
                $data = ['version' => $data];
            }
            $installed = $modx->getIterator(modTransportPackage::class, ['package_name' => $name]);
            /** @var modTransportPackage $package */
            foreach ($installed as $package) {
                if ($package->compareVersion($data['version'], '<=')) {
                    continue(2);
                }
            }
            $modx->log(modX::LOG_LEVEL_INFO, "Trying to install <b>{$name}</b>. Please wait...");
            $response = $installPackage($name, $data);
            if (is_array($response)) {
                $level = $response['success']
                    ? modX::LOG_LEVEL_INFO
                    : modX::LOG_LEVEL_ERROR;
                $modx->log($level, $response['message']);
            }
        }

        // Create property sets for Jevix
        /** @var modSnippet $snippet */
        if ($snippet = $modx->getObject(modSnippet::class, ['name' => 'Jevix'])) {
            if (!$prop_ticket = $modx->getObject(modPropertySet::class, ['name' => 'Ticket'])) {
                $prop_ticket = $modx->newObject(modPropertySet::class);
                $prop_ticket->fromArray([
                    'name' => 'Ticket',
                    'description' => 'Filter rules for Tickets2',
                    'properties' => [
                        'cfgAllowTagParams' => [
                            'name' => 'cfgAllowTagParams',
                            'desc' => 'cfgAllowTagParams',
                            'type' => 'textfield',
                            'options' => [],
                            'lexicon' => 'jevix:properties',
                            'area' => '',
                            'value' => '{"pre":{"class":["prettyprint"]},"cut":{"title":["#text"]},"a":["title","href"],"img":{"0":"src","alt":"#text","1":"title","align":["right","left","center"],"width":"#int","height":"#int","hspace":"#int","vspace":"#int"}}',
                        ],
                        'cfgAllowTags' => [
                            'name' => 'cfgAllowTags',
                            'desc' => 'cfgAllowTags',
                            'type' => 'textfield',
                            'options' => [],
                            'lexicon' => 'jevix:properties',
                            'area' => '',
                            'value' => 'a,img,i,b,u,em,strong,li,ol,ul,sup,abbr,acronym,h1,h2,h3,h4,h5,h6,cut,br,pre,code,kbd,s,blockquote,table,tr,th,td,video',
                        ],
                        'cfgSetTagChilds' => [
                            'name' => 'cfgSetTagChilds',
                            'desc' => 'cfgSetTagChilds',
                            'type' => 'textfield',
                            'options' => [],
                            'lexicon' => 'jevix:properties',
                            'area' => '',
                            'value' => '[["ul",["li"],false,true],["ol",["li"],false,true],["table",["tr"],false,true],["tr",["td","th"],false,true]]',
                        ],
                        'cfgSetAutoReplace' => [
                            'name' => 'cfgSetAutoReplace',
                            'desc' => 'cfgSetAutoReplace',
                            'type' => 'textfield',
                            'options' => [],
                            'lexicon' => 'jevix:properties',
                            'area' => '',
                            'value' => '[["+/-","(c)","(с)","(r)","(C)","(С)","(R)","<code","code>"],["±","©","©","®","©","©","®","<pre class=\\"prettyprint\\"","pre>"]]',
                        ],
                        'cfgSetTagShort' => [
                            'name' => 'cfgSetTagShort',
                            'desc' => 'cfgSetTagShort',
                            'type' => 'textfield',
                            'options' => [],
                            'lexicon' => 'jevix:properties',
                            'area' => '',
                            'value' => 'br,img,cut',
                        ],
                        'cfgSetTagPreformatted' => [
                            'name' => 'cfgSetTagPreformatted',
                            'desc' => 'cfgSetTagPreformatted',
                            'type' => 'textfield',
                            'options' => [],
                            'lexicon' => 'jevix:properties',
                            'area' => '',
                            'value' => 'pre,code,kbd',
                        ],
                    ],
                ], '', true, true);
                if ($prop_ticket->save() && $snippet->addPropertySet($prop_ticket)) {
                    $modx->log(xPDO::LOG_LEVEL_INFO, '[Tickets2] Property set "Ticket" for snippet <b>Jevix</b> was created');
                } else {
                    $modx->log(xPDO::LOG_LEVEL_ERROR, '[Tickets2] Could not create property set "Ticket" for <b>Jevix</b>');
                }
            }

            if (!$prop_comment = $modx->getObject(modPropertySet::class, ['name' => 'Comment'])) {
                $prop_comment = $modx->newObject(modPropertySet::class);
                $prop_comment->fromArray([
                    'name' => 'Comment',
                    'description' => 'Filter rules for Tickets2 comments',
                    'properties' => [
                        'cfgAllowTagParams' => [
                            'name' => 'cfgAllowTagParams',
                            'desc' => 'cfgAllowTagParams',
                            'type' => 'textfield',
                            'options' => [],
                            'lexicon' => 'jevix:properties',
                            'area' => '',
                            'value' => '{"pre":{"class":["prettyprint"]},"a":["title","href"],"img":{"0":"src","alt":"#text","1":"title","align":["right","left","center"],"width":"#int","height":"#int","hspace":"#int","vspace":"#int"}}',
                        ],
                        'cfgAllowTags' => [
                            'name' => 'cfgAllowTags',
                            'desc' => 'cfgAllowTags',
                            'type' => 'textfield',
                            'options' => [],
                            'lexicon' => 'jevix:properties',
                            'area' => '',
                            'value' => 'a,img,i,b,u,em,strong,li,ol,ul,sup,abbr,acronym,br,pre,code,kbd,s,blockquote',
                        ],
                        'cfgSetTagChilds' => [
                            'name' => 'cfgSetTagChilds',
                            'desc' => 'cfgSetTagChilds',
                            'type' => 'textfield',
                            'options' => [],
                            'lexicon' => 'jevix:properties',
                            'area' => '',
                            'value' => '[["ul",["li"],false,true],["ol",["li"],false,true]]',
                        ],
                        'cfgSetAutoReplace' => [
                            'name' => 'cfgSetAutoReplace',
                            'desc' => 'cfgSetAutoReplace',
                            'type' => 'textfield',
                            'options' => [],
                            'lexicon' => 'jevix:properties',
                            'area' => '',
                            'value' => '[["+/-","(c)","(с)","(r)","(C)","(С)","(R)","<code","code>"],["±","©","©","®","©","©","®","<pre class=\\"prettyprint\\"","pre>"]]',
                        ],
                        'cfgSetTagShort' => [
                            'name' => 'cfgSetTagShort',
                            'desc' => 'cfgSetTagShort',
                            'type' => 'textfield',
                            'options' => [],
                            'lexicon' => 'jevix:properties',
                            'area' => '',
                            'value' => 'br,img',
                        ],
                        'cfgSetTagPreformatted' => [
                            'name' => 'cfgSetTagPreformatted',
                            'desc' => 'cfgSetTagPreformatted',
                            'type' => 'textfield',
                            'options' => [],
                            'lexicon' => 'jevix:properties',
                            'area' => '',
                            'value' => 'pre,code,kbd',
                        ],
                    ],
                ], '', true, true);
                if ($prop_comment->save() && $snippet->addPropertySet($prop_comment)) {
                    $modx->log(xPDO::LOG_LEVEL_INFO, '[Tickets2] Property set "Comment" for snippet <b>Jevix</b> was created');
                } else {
                    $modx->log(xPDO::LOG_LEVEL_ERROR, '[Tickets2] Could not create property set "Comment" for <b>Jevix</b>');
                }
            }
        }

        $success = true;
        break;

    case xPDOTransport::ACTION_UNINSTALL:
        $success = true;
        break;
}

return $success; 