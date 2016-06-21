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

use ContaoBlackForest\Contao\Core\DcGeneral\Builder\DataDefinitionsBuilder;
use ContaoBlackForest\Contao\Core\DcGeneral\Controller\ActionController;
use ContaoBlackForest\Contao\Core\DcGeneral\Controller\ModelController;
use ContaoBlackForest\Contao\Core\DcGeneral\Controller\WidgetController;
use ContaoBlackForest\Contao\Core\DcGeneral\Controller\PaletteController;

return array(
    new ActionController(),
    new ModelController(),
    new DataDefinitionsBuilder(),
    new PaletteController(),
    new WidgetController()
);
