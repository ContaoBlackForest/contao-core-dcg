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
 * Class MemberService
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral\Service
 */
class MemberService extends AbstractService
{
    /**
     * {@inheritDoc}
     */
    public function getModuleName()
    {
        return 'member';
    }

    /**
     * {@inheritDoc}
     */
    public function getPermittedDataProvider()
    {
        return array(
            'tl_member'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getToggleOperation()
    {
        return array(
            'tl_news' => array(
                'toggle' => array(
                    'property' => 'disable'
                )
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getInverseToggleOperation()
    {
        return array();
    }


    /**
     * {@inheritDoc}
     */
    public function getListLabelConfig()
    {
        return array();
    }
}
