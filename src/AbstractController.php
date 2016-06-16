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
     * @param $dataProvider | string the dca name (e.g. tl_article)
     *
     * @return mixed
     */
    public function initialize($dataProvider)
    {
        if (!$this->isModuleLoaded()) {
            return;
        }

        $this->changeDataContainerToDcGeneral($dataProvider);
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
     * @param $dataProvider | string the data provider (e.g. tl_article)
     */
    protected function changeDataContainerToDcGeneral($dataProvider)
    {
        if (!in_array($dataProvider, $this->getPermittedDataProvider())) {
            return;
        }

        $this->replaceDataContainerDriver($dataProvider);
        $this->setAsService($dataProvider);
    }

    /**
     * Replace the data container driver
     *
     * @param $dataProvider | string the data provider (e.g. tl_article)
     */
    protected function replaceDataContainerDriver($dataProvider)
    {
        if ($GLOBALS['TL_DCA'][$dataProvider]['config']['dataContainer'] === 'General') {
            return;
        }

        $GLOBALS['TL_DCA'][$dataProvider]['config']['dataContainer'] = 'General';
    }

    /**
     * Set as service
     *
     * @param $dataProvider | string the data provider (e.g. tl_article)
     */
    protected function setAsService($dataProvider)
    {
        global $container;

        $serviceName = 'dc-general.table_to_general.' . $dataProvider;
        if (isset($container[$serviceName])) {
            return;
        }

        $container[$serviceName] = $this;
    }
}
