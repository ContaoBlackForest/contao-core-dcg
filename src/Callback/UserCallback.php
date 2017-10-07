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


use Contao\BackendUser;
use Contao\Config;
use Contao\Controller;
use Contao\Database;
use Contao\Environment;
use Contao\Image;
use Contao\Input;
use Contao\System;
use ContaoBlackForest\Contao\Core\DcGeneral\Service\TableToGeneralService;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\Callbacks;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use Exception;

class UserCallback
{
    /**
     * Initialize the contao core module to dc general
     *
     * @param $dataProvider | string the dca name (e.g. tl_article)
     *
     * @return void
     */

    public function initialize($dataProvider)
    {
        global $container;

        /** @var TableToGeneralService $service */
        $service = $container['dc-general.table_to_general'];

        if (!($controller = $service->getDataProviderController('tl_user'))
            || !in_array($dataProvider, $controller->getPermittedDataProvider())
        ) {
            return;
        }

        $this->supplantModelOperationButtonCallbacks($dataProvider);
    }

    /**
     * Supplant model operation button callbacks.
     *
     * @param $dataProvider | string the dca name (e.g. tl_article)
     *
     * @return void
     */
    private function supplantModelOperationButtonCallbacks($dataProvider)
    {
        foreach ($GLOBALS['TL_DCA'][$dataProvider]['list']['operations'] as $commandName => $commandConfig) {
            if (false === array_key_exists('button_callback', $commandConfig)) {
                continue;
            }

            $GLOBALS['TL_DCA'][$dataProvider]['list']['operations'][$commandName]['button_callback'] = array(
                get_class($this),
                'replaceRowIdWithSerializedModelId'
            );
        }
    }

    public function replaceRowIdWithSerializedModelId(
        $arrRow,
        $href,
        $label,
        $title,
        $icon,
        $attributes,
        $strTable,
        $arrRootIds,
        $arrChildRecordIds,
        $blnCircularReference,
        $strPrevious,
        $strNext
    ) {
        $modelId = new ModelId($strTable, $arrRow['id']);

        switch ($icon) {
            case 'edit.gif':
                $callback     = array('tl_user', 'editUser');
                $arrRow['id'] = $modelId->getSerialized();

                break;
            case 'copy.gif':
                $callback     = array('tl_user', 'copyUser');
                $arrRow['id'] = $modelId->getSerialized();

                break;
            case 'delete.gif':
                $callback     = array('tl_user', 'deleteUser');
                $arrRow['id'] = $modelId->getSerialized();

                break;
            case 'visible.gif':
                $callback = array('tl_user', 'toggleIcon');

                break;
            case 'su.gif':
                $callback     = array(get_class($this), 'switchUser');
                $arrRow['id'] = $modelId->getSerialized();

                break;

            default:
        }

        $callbackArgs = array(
            $arrRow,
            $href,
            $label,
            $title,
            $icon,
            $attributes,
            $strTable,
            $arrRootIds,
            $arrChildRecordIds,
            $blnCircularReference,
            $strPrevious,
            $strNext
        );

        return Callbacks::callArgs($callback, $callbackArgs);
    }

    /**
     * Generate a "switch account" button and return it as string
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     *
     * @return string
     *
     * @throws Exception
     */
    public function switchUser($row, $href, $label, $title, $icon)
    {
        $user     = BackendUser::getInstance();
        $database = Database::getInstance();

        if (!$user->isAdmin) {
            return '';
        }

        if (Input::get('key') == 'su' && Input::get('id')) {
            $objUser = $database->prepare("SELECT id, username FROM tl_user WHERE id=?")
                ->execute(ModelId::fromSerialized(Input::get('id'))->getId());

            if (!$objUser->numRows) {
                throw new Exception('Invalid user ID ' . Input::get('id'));
            }

            $database->prepare("UPDATE tl_session SET pid=?, su=1 WHERE hash=?")
                ->execute(
                    $objUser->id,
                    sha1(
                        session_id() . (!Config::get('disableIpCheck') ? Environment::get('ip') : '') . 'BE_USER_AUTH'
                    )
                );

            System::log(
                'User "' . $user->username . '" has switched to user "' . $objUser->username . '"',
                __METHOD__,
                TL_ACCESS
            );

            Controller::redirect('contao/main.php');
        }

        return '<a href="' . Controller::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title)
               . '">' . Image::getHtml($icon, $label) . '</a> ';
    }
}
