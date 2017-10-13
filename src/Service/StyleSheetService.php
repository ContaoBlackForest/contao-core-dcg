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

/**
 * Class StyleSheetService
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral\Service
 */
class StyleSheetService extends AbstractService
{
    /**
     * {@inheritDoc}
     */
    public function getModuleName()
    {
        return 'themes';
    }

    /**
     * {@inheritDoc}
     */
    public function getPermittedDataProvider()
    {
        return array(
            'tl_theme',
            'tl_style_sheet',
            'tl_style'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getToggleOperation()
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function getInverseToggleOperation()
    {
        return array(
            'tl_style' => array(
                'toggle' => array(
                    'property' => 'invisible'
                )
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getListLabelConfig()
    {
        return array();
    }
}
