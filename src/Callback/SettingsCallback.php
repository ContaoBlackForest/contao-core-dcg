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

namespace ContaoBlackForest\Contao\Core\DcGeneral\Callback;

use Contao\Image;

class SettingsCallback
{
    /**
     * Initialize the contao core module to dc general
     *
     * @param $dataProvider | string the dca name (e.g. tl_article)
     *
     * @return array
     */

    public function getActivationOptions()
    {
        $modules = array();
        foreach ($GLOBALS['BE_MOD'] as $moduleCategory) {
            $modules = array_merge($modules, $moduleCategory);
        }

        $options = array();
        foreach ($modules as $moduleName => $moduleSetting) {
            if (in_array(
                $moduleName,
                array(
                    'tpl_editor',
                    'files',
                    'log',
                    'settings',
                    'maintenance',
                    'undo',
                    'composer',
                    'autoload',
                    'extension',
                    'labels',
                )
            )) {
                continue;
            }

            $optionName = '';
            if (isset($moduleSetting['icon'])) {
                $optionName .= Image::getHtml($moduleSetting['icon']) . ' ';
            } else {
                $optionName .= Image::getHtml('system/themes/default/images/' . $moduleName . '.gif');
            }

            $optionName .= '<span style="margin-left: 22px; margin-top: -18px;">';
            $optionName .= isset($GLOBALS['TL_LANG']['MOD'][$moduleName])
                ? $GLOBALS['TL_LANG']['MOD'][$moduleName][0] : $moduleName;
            $optionName .= '</span>';

            $options[$moduleName] = $optionName;
        }

        return $options;
    }
}
