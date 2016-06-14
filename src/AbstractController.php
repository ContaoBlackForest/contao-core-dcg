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

use Contao\Input;

/**
 * Class AbstractController
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral
 */
abstract class AbstractController implements ControllerInterface
{
    /**
     * Initialize the contao core module to dc general
     *
     * @param $dataProvider | the dca name (e.g. tl_article)
     *
     * @return mixed
     */
    public function initialize($dataProvider)
    {
        if (!$this->isModuleLoaded()) {
            return;
        }

        $this->changeDataContainerDriver($dataProvider);
    }

    /**
     * Show if this module loaded
     *
     * @return bool
     */
    protected function isModuleLoaded()
    {
        if (Input::get('do') === $this->getModuleName()) {
            return true;
        }

        return false;
    }

    /**
     * Change the data container to dc general if this data provider permitted
     *
     * @param $dataProvider
     */
    protected function changeDataContainerDriver($dataProvider)
    {
        if (!in_array($dataProvider, $this->getPermittedDataProvider())) {
            return;
        }

        $GLOBALS['TL_DCA'][$dataProvider]['config']['dataContainer'] = 'General';
    }
}
