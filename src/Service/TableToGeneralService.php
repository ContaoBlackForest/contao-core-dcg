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

namespace ContaoBlackForest\Contao\Core\DcGeneral\Service;


use ContaoBlackForest\Contao\Core\DcGeneral\AbstractController;

/**
 * Class TableToGeneralService
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral\Service
 */
class TableToGeneralService
{
    /**
     * Get the data provider controller from service container
     *
     * @param $containerName
     *
     * @return AbstractController|null
     */
    public function getDataProviderController($containerName)
    {
        global $container;

        $serviceName = 'dc-general.table_to_general.' . $containerName;
        if (!isset($container[$serviceName])) {
            return null;
        }

        /** @var AbstractController $controller */
        $controller = $container[$serviceName];

        return $controller;
    }

}
