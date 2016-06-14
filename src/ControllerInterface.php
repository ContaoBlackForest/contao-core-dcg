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

namespace ContaoBlackForest\Contao\Core\DcGeneral;

/**
 * Interface ControllerInterface
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral
 */
interface ControllerInterface
{
    /**
     * Get the module name.
     *
     * @return string
     */
    public function getModuleName();

    /**
     * This returns permitted data provider.
     * Note the array must be in the right relation.
     *
     * @return array
     */
    public function getPermittedDataProvider();
}
