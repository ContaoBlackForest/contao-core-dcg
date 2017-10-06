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
use ContaoBlackForest\Contao\Core\DcGeneral\DataContainer\FormFieldController;
use ContaoBlackForest\Contao\Core\DcGeneral\DataContainer\MemberController;
use ContaoBlackForest\Contao\Core\DcGeneral\DataContainer\ModelController;
use ContaoBlackForest\Contao\Core\DcGeneral\DataContainer\ModuleController;
use ContaoBlackForest\Contao\Core\DcGeneral\DataContainer\WidgetController;

return array(
    new ModelController(),
    new DataDefinitionsBuilder(),
    new WidgetController(),
    new FormFieldController(),
    new ModuleController(),
    new MemberController()
);
