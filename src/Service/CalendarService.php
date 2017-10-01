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
 * Class CalendarService
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral\Service
 */
class CalendarService extends AbstractService
{
    /**
     * {@inheritDoc}
     */
    public function getModuleName()
    {
        return 'calendar';
    }

    /**
     * {@inheritDoc}
     */
    public function getPermittedDataProvider()
    {
        return array(
            'tl_calendar',
            'tl_calendar_events',
            'tl_content'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getToggleOperation()
    {
        return array(
            'tl_calendar_events' => array(
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
            'tl_calendar' => array(
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
