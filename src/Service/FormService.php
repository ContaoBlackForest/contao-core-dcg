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
 * Class FormService
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral\Service
 */
class FormService extends AbstractService
{
    /**
     * {@inheritDoc}
     */
    public function getModuleName()
    {
        return 'form';
    }

    /**
     * {@inheritDoc}
     */
    public function getPermittedDataProvider()
    {
        return array(
            'tl_form',
            'tl_form_field'
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
            'tl_form_field' => array(
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
        return array(
            'tl_form' => array(
                'fields' => array('title', 'formID'),
                'format' => '%s <span style="color:#b3b3b3;padding-left:3px">[%s]</span>',
            ),
        );
    }
}
