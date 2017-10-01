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
 * Class NewsletterRecipientsService
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral\Service
 */
class NewsletterRecipientsService extends AbstractService
{
    /**
     * {@inheritDoc}
     */
    public function getModuleName()
    {
        return 'newsletter';
    }

    /**
     * {@inheritDoc}
     */
    public function getPermittedDataProvider()
    {
        return array(
            'tl_newsletter_channel',
            'tl_newsletter_recipients'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getToggleOperation()
    {
        return array(
            'tl_newsletter_recipients' => array(
                'toggle' => array(
                    'property' => 'active'
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
