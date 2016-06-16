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
 * Class NewsController
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral
 */
class NewsController extends AbstractController implements ControllerInterface
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
}
