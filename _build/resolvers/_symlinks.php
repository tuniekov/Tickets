<?php

/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx = $transport->xpdo;

    $dev = MODX_BASE_PATH . 'Extras/Tickets2/';
    /** @var xPDOCacheManager $cache */
    $cache = $modx->getCacheManager();
    if (file_exists($dev) && $cache) {
        if (!is_link($dev . 'assets/components/tickets2')) {
            $cache->deleteTree(
                $dev . 'assets/components/tickets2/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_ASSETS_PATH . 'components/tickets2/', $dev . 'assets/components/tickets2');
        }
        if (!is_link($dev . 'core/components/tickets2')) {
            $cache->deleteTree(
                $dev . 'core/components/tickets2/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_CORE_PATH . 'components/tickets2/', $dev . 'core/components/tickets2');
        }
    }
}

return true;
