<?php

/**
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 */

// Load the classes
$modx->addPackage('Tickets2\Model', $namespace['path'] . 'src/', null, 'Tickets2\\');

$modx->services->add('Tickets2', function ($c) use ($modx) {
    return new Tickets2\Tickets2($modx);
});
