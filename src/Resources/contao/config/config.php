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

$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('ContaoBlackForest\Contao\Core\DcGeneral\Service\NewsArchiveService', 'initialize');
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('ContaoBlackForest\Contao\Core\DcGeneral\Service\NewsFeedService', 'initialize');
