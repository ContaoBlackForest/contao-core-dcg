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
 * Class NewsArchiveService
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral\Service
 */
class NewsArchiveService extends AbstractService
{
    /**
     * {@inheritDoc}
     */
    public function getModuleName()
    {
        return 'news';
    }

    /**
     * {@inheritDoc}
     */
    public function getPermittedDataProvider()
    {
        return array(
            'tl_news_archive',
            'tl_news',
            'tl_content'
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
                    'property' => 'published'
                ),
                'feature' => array(
                    'property' => 'featured',
                    'icon_disabled' => 'featured_.gif'
                )
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getInverseToggleOperation()
    {
        return array(
            'tl_content' => array(
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
            'tl_news' => array(
                'fields' => array('id', 'headline'),
                'format' => 'News::%s %s',
            ),
            'tl_content' => array(
                'fields' => array('id', 'type'),
                'format' => 'Content Element::%s %s',
            ),
        );
    }
}
