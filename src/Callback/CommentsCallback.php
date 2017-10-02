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
use Contao\Cache;
use Contao\Comments;
use Contao\CommentsModel;
use Contao\Controller;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\Session;
use Contao\Versions;
use ContaoBlackForest\Contao\Core\DcGeneral\Service\TableToGeneralService;
use ContaoCommunityAlliance\DcGeneral\Contao\Callback\Callbacks;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;

class CommentsCallback
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

        if (!$controller = $service->getDataProviderController('tl_comments')) {
            return;
        }

        $this->supplantModelOperationButtonCallbacks($dataProvider);
        $this->supplantOnLoadCallbacks($dataProvider);
        $this->supplantPropertySaveCallbacks($dataProvider);
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
        $modelId      = new ModelId($strTable, $arrRow['id']);
        $arrRow['id'] = $modelId->getSerialized();

        switch ($icon) {
            case 'edit.gif':
                $callback = array('tl_comments', 'editComment');

                break;
            case 'delete.gif':
                $callback = array('tl_comments', 'deleteComment');

                break;
            case 'visible.gif':
                $callback = array(get_class($this), 'toggleIcon');

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

    private function supplantOnLoadCallbacks($dataProvider)
    {
        foreach ($GLOBALS['TL_DCA'][$dataProvider]['config']['onload_callback'] as $index => $callback) {
            if ('checkPermission' !== $callback[1]) {
                return;
            }

            $GLOBALS['TL_DCA'][$dataProvider]['config']['onload_callback'][$index] =
                array(get_class($this), $callback[1]);
        }
    }

    /**
     * Check permissions to edit table tl_comments
     */
    public function checkPermission()
    {
        $database          = Database::getInstance();
        $sessionController = Session::getInstance();

        switch (Input::get('act')) {
            case 'select':
            case 'show':
                // Allow
                break;

            case 'edit':
            case 'delete':
            case 'toggle':
                $objComment = $database->prepare("SELECT id, parent, source FROM tl_comments WHERE id=?")
                    ->limit(1)
                    ->execute(ModelId::fromSerialized(Input::get('id'))->getId());

                if ($objComment->numRows < 1) {
                    Controller::log('Comment ID ' . Input::get('id') . ' does not exist', __METHOD__, TL_ERROR);
                    Controller::redirect('contao/main.php?act=error');
                }

                if (!$this->isAllowedToEditComment($objComment->parent, $objComment->source)) {
                    Controller::log(
                        'Not enough permissions to ' . Input::get('act') . ' comment ID ' . Input::get('id')
                        . ' (parent element: ' . $objComment->source . ' ID ' . $objComment->parent . ')',
                        __METHOD__,
                        TL_ERROR
                    );
                    Controller::redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $sessionController->getData();

                if (!is_array($session['CURRENT']['IDS']) || empty($session['CURRENT']['IDS'])) {
                    break;
                }

                $objComment = $database->execute(
                    "SELECT id, parent, source FROM tl_comments WHERE id IN(" . implode(
                        ',',
                        array_map(
                            'intval',
                            $session['CURRENT']['IDS']
                        )
                    ) . ")"
                );

                while ($objComment->next()) {
                    if (!$this->isAllowedToEditComment($objComment->parent, $objComment->source)
                        && ($key = array_search($objComment->id, $session['CURRENT']['IDS'])) !== false) {
                        unset($session['CURRENT']['IDS'][$key]);
                    }
                }

                $session['CURRENT']['IDS'] = array_values($session['CURRENT']['IDS']);
                $sessionController->setData($session);
                break;

            default:
                if (strlen(Input::get('act'))) {
                    Controller::log('Invalid command "' . Input::get('act') . '"', __METHOD__, TL_ERROR);
                    Controller::redirect('contao/main.php?act=error');
                }
                break;
        }
    }

    /**
     * Check whether the user is allowed to edit a comment
     *
     * @param integer $intParent
     * @param string  $strSource
     *
     * @return boolean
     */
    protected function isAllowedToEditComment($intParent, $strSource)
    {
        $user     = BackendUser::getInstance();
        $database = Database::getInstance();

        if ($user->isAdmin) {
            return true;
        }

        $strKey = __METHOD__ . '-' . $strSource . '-' . $intParent;

        // Load cached result
        if (Cache::has($strKey)) {
            return Cache::get($strKey);
        }

        // Order deny,allow
        Cache::set($strKey, false);

        switch ($strSource) {
            case 'tl_content':
                $objPage = $database->prepare(
                    "SELECT * FROM tl_page WHERE id=(SELECT pid FROM tl_article WHERE id=(SELECT pid FROM tl_content WHERE id=?))"
                )
                    ->limit(1)
                    ->execute($intParent);

                // Do not check whether the page is mounted (see #5174)
                if ($objPage->numRows > 0 && $user->isAllowed(BackendUser::CAN_EDIT_ARTICLES, $objPage->row())) {
                    Cache::set($strKey, true);
                }
                break;

            case 'tl_page':
                $objPage = $database->prepare("SELECT * FROM tl_page WHERE id=?")
                    ->limit(1)
                    ->execute($intParent);

                // Do not check whether the page is mounted (see #5174)
                if ($objPage->numRows > 0 && $user->isAllowed(BackendUser::CAN_EDIT_PAGE, $objPage->row())) {
                    Cache::set($strKey, true);
                }
                break;

            case 'tl_news':
                $objArchive = $database->prepare("SELECT pid FROM tl_news WHERE id=?")
                    ->limit(1)
                    ->execute($intParent);

                // Do not check the access to the news module (see #5174)
                if ($objArchive->numRows > 0 && $user->hasAccess($objArchive->pid, 'news')) {
                    Cache::set($strKey, true);
                }
                break;

            case 'tl_calendar_events':
                $objCalendar = $database->prepare("SELECT pid FROM tl_calendar_events WHERE id=?")
                    ->limit(1)
                    ->execute($intParent);

                // Do not check the access to the calendar module (see #5174)
                if ($objCalendar->numRows > 0 && $user->hasAccess($objCalendar->pid, 'calendars')) {
                    Cache::set($strKey, true);
                }
                break;

            case 'tl_faq':
                // Do not check the access to the FAQ module (see #5174)
                Cache::set($strKey, true);
                break;

            default:
                // HOOK: support custom modules
                if (isset($GLOBALS['TL_HOOKS']['isAllowedToEditComment'])
                    && is_array(
                        $GLOBALS['TL_HOOKS']['isAllowedToEditComment']
                    )) {
                    foreach ($GLOBALS['TL_HOOKS']['isAllowedToEditComment'] as $callback) {
                        if (Callbacks::callArgs($callback, array($intParent, $strSource)) === true) {
                            Cache::set($strKey, true);
                            break;
                        }
                    }
                }
                break;
        }

        return Cache::get($strKey);
    }

    private function supplantPropertySaveCallbacks($dataProvider)
    {
        foreach ($GLOBALS['TL_DCA'][$dataProvider]['fields'] as $propertyName => $propertyConfig) {
            if (!in_array($propertyName, array('published'))) {
                continue;
            }

            if (!array_key_exists('save_callback', $propertyConfig)) {
                continue;
            }

            switch ($propertyName) {
                case 'published':
                    foreach ($propertyConfig['save_callback'] as $callbackIndex => $callback) {
                        if ('sendNotifications' !== $callback[1]) {
                            continue;
                        }

                        $propertyConfig['save_callback'][$callbackIndex] = array(get_class($this), $callback[1]);
                    }
                    break;
                default:
            }

            $GLOBALS['TL_DCA'][$dataProvider]['fields'][$propertyName] = $propertyConfig;
        }
    }

    /**
     * Send out the new comment notifications
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function sendNotifications($varValue)
    {
        if ($varValue) {
            Comments::notifyCommentsSubscribers(
                CommentsModel::findByPk(ModelId::fromSerialized(Input::get('id'))->getId())
            );
        }

        return $varValue;
    }

    /**
     * Return the "toggle visibility" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $user = BackendUser::getInstance();

        if (strlen(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
            Controller::redirect(Controller::getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$user->hasAccess('tl_comments::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.gif';
        }

        if (!$this->isAllowedToEditComment($row['parent'], $row['source'])) {
            return Image::getHtml($icon) . ' ';
        }

        return '<a href="' . Controller::addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>'
               . Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';
    }

    /**
     * Disable/enable a user group
     *
     * @param integer       $intId
     * @param boolean       $blnVisible
     * @param DataContainer $dc
     */
    public function toggleVisibility($intId, $blnVisible, DataContainer $dc = null)
    {
        $user = BackendUser::getInstance();
        $database = Database::getInstance();

        // Set the ID and action
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        if ($dc) {
            $dc->id = $intId; // see #8043
        }

        $this->checkPermission();

        // Check the field access
        if (!$user->hasAccess('tl_comments::published', 'alexf')) {
            Controller::log(
                'Not enough permissions to publish/unpublish comment ID "' . $intId . '"',
                __METHOD__,
                TL_ERROR
            );
            Controller::redirect('contao/main.php?act=error');
        }

        $objVersions = new Versions('tl_comments', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_comments']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_comments']['fields']['published']['save_callback'] as $callback) {
                if (is_array($callback)) {
                    $blnVisible = Callbacks::callArgs(
                        $callback,
                        array(
                            $blnVisible,
                            ($dc
                                ?: $this->getClassObject(
                                    'tl_comments'
                                ))
                        )
                    );
                } elseif (is_callable($callback)) {
                    //Fixme Callable callback
                    $blnVisible = $callback($blnVisible, ($dc ?: $this->getClassObject('tl_comments')));
                }
            }
        }

        // Update the database
        $database->prepare(
            "UPDATE tl_comments SET tstamp=" . time() . ", published='" . ($blnVisible ? '1' : '') . "' WHERE id=?"
        )
            ->execute(ModelId::fromSerialized($intId)->getId());

        $objVersions->create();
    }

    /**
     * Get class object
     *
     * @param string $className The class name.
     *
     * @return mixed
     */
    private function getClassObject($className)
    {
        $class = new \ReflectionClass($className);

        // Fetch singleton instance.
        if ($class->hasMethod('getInstance')) {
            $getInstanceMethod = $class->getMethod('getInstance');

            if ($getInstanceMethod->isStatic()) {
                $classObject = $getInstanceMethod->invoke(null);
                return $classObject;
            }
        }

        // Create a new instance.
        $constructor = $class->getConstructor();

        if (!$constructor || $constructor->isPublic()) {
            $classObject = $class->newInstance();
        } else {
            // Graceful fallback, to prevent access violation to non-public \Backend::__construct().
            $classObject = $class->newInstanceWithoutConstructor();
            $constructor->setAccessible(true);
            $constructor->invoke($classObject);
        }

        return $classObject;
    }
}
