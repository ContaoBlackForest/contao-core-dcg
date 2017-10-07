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

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .=
    ';{core_to_dcg_legend},coreToDcgDebugMode,coreToDcgActivation';

$GLOBALS['TL_DCA']['tl_settings']['fields'] = array_merge(
    $GLOBALS['TL_DCA']['tl_settings']['fields'],
    array(
        'coreToDcgDebugMode'  => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_settings']['coreToDcgDebugMode'],
            'inputType' => 'checkbox',
            'eval'      => array('tl_class' => 'w50')
        ),
        'coreToDcgActivation' => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_settings']['coreToDcgActivation'],
            'default'          => array('layout.css', 'responsive.css'),
            'exclude'          => true,
            'inputType'        => 'checkboxWizard',
            'options_callback' =>
                array('ContaoBlackForest\Contao\Core\DcGeneral\Callback\SettingsCallback', 'getActivationOptions'),
            'eval'             => array('multiple' => true, 'tl_class' => 'w50 clr'),
        )
    )
);
