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

    /**
     * This returns toggle operation.
     * You must define the data provider, and there the operation for inverse.
     * 
     * @return array
     */
    public function getToggleOperation();

    /**
     * This returns toggle operation there have inverse function.
     * You must define the data provider, and there the operation for inverse.
     * 
     * @return array
     */
    public function getInverseToggleOperation();

    /**
     * This returns label for list config.
     * You must define the data provider, and there label config.
     *
     * @return array
     */
    public function getListLabelConfig();
}
