<?php

/**
 * Contao Core DC General
 *
 * Copyright (C) ContaoBlackForest
 *
 * @package   contao-core-dcg
 * @author    Sven Baumann <baumann.sv@gmail.com>
 * @author    Dominik Tomasi <dominik.tomasi@gmail.com>
 * @license   GNU/LGPL
 * @copyright Copyright 2016 ContaoBlackForest
 */


use ContaoBlackForest\Contao\Core\DcGeneral\Service\TableToGeneralService;

/** @var \DependencyInjection\Container\PimpleGate $container */

$container->provideSymfonyService('dc-general.table_to_general');
$container->provideSymfonyService('dc-general.table_to_general.tl_news');
$container->provideSymfonyService('dc-general.table_to_general.tl_news_archive');
