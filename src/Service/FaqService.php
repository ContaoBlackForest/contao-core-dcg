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
 * Class FaqService
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral\Service
 */
class FaqService extends AbstractService
{
    /**
     * {@inheritDoc}
     */
    public function getModuleName()
    {
        return 'faq';
    }

    /**
     * {@inheritDoc}
     */
    public function getPermittedDataProvider()
    {
        return array(
            'tl_faq_category',
            'tl_faq'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getToggleOperation()
    {
        return array(
            'tl_faq' => array(
                'toggle' => array(
                    'property' => 'published'
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
        return array(
            'tl_faq_category' => array(
                'fields' => array('title'),
                'format' => '%s',
            ),
            'tl_content' => array(
                'fields' => array('id', 'type'),
                'format' => 'Content Element::%s %s',
            ),
        );
    }
}
