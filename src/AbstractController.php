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

use Contao\Input;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultModel;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class AbstractController
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral
 */
abstract class AbstractController implements ControllerInterface
{
    /**
     * Initialize the contao core module to dc general
     *
     * @param $dataProvider | string the dca name (e.g. tl_article)
     *
     * @return mixed
     */
    public function initialize($dataProvider)
    {
        if (!$this->isModuleLoaded()) {
            return;
        }

        $this->changeDataContainerToDcGeneral($dataProvider);
    }

    /**
     * Show if this module loaded
     *
     * @return bool
     */
    protected function isModuleLoaded()
    {
        if (Input::get('do') === $this->getModuleName()) {
            return true;
        }

        return false;
    }

    /**
     * Change the data container to dc general if this data provider permitted
     *
     * @param $dataProvider | string the data provider (e.g. tl_article)
     */
    protected function changeDataContainerToDcGeneral($dataProvider)
    {
        if (!in_array($dataProvider, $this->getPermittedDataProvider())) {
            return;
        }

        $this->replaceDataContainerDriver($dataProvider);
        $this->setAsService($dataProvider);
        $this->handleDataContainerConfigCallbacks($dataProvider);
    }

    /**
     * Replace the data container driver
     *
     * @param $dataProvider | string the data provider (e.g. tl_article)
     */
    protected function replaceDataContainerDriver($dataProvider)
    {
        if ($GLOBALS['TL_DCA'][$dataProvider]['config']['dataContainer'] === 'General') {
            return;
        }

        $GLOBALS['TL_DCA'][$dataProvider]['config']['dataContainer'] = 'General';
    }

    /**
     * Set as service
     *
     * @param $dataProvider | string the data provider (e.g. tl_article)
     */
    protected function setAsService($dataProvider)
    {
        global $container;

        $serviceName = 'dc-general.table_to_general.' . $dataProvider;
        if (isset($container[$serviceName])) {
            return;
        }

        $container[$serviceName] = $this;
    }

    /**
     * handle callbacks from data container config.
     *
     * @param $dataProvider | string the data provider (e.g. tl_article)
     *
     * @see LegacyDcaDataDefinitionBuilder::253
     *
     * Fixme by DC General. By some callbacks the model don´t available. e.g. tl_news => config => onsubmit_callback => adjustTime
     */
    protected function handleDataContainerConfigCallbacks($dataProvider)
    {
        global $container;

        /** @var EventDispatcher $dispatcher */
        $dispatcher = $container['event-dispatcher'];

        $callbackNames = array('onsubmit_callback');

        foreach ($callbackNames as $callbackName) {
            $callbackEvent = null;

            switch ($callbackName) {
                case 'onsubmit_callback':
                    $callbackEvent = PostPersistModelEvent::NAME;
                    break;

                default:
            }

            if (!$callbackEvent
                || !array_key_exists($callbackName, $GLOBALS['TL_DCA'][$dataProvider]['config'])
            ) {
                continue;
            }

            foreach ($GLOBALS['TL_DCA'][$dataProvider]['config'][$callbackName] as $callback) {
                $dispatcher->addListener(
                    $callbackEvent,
                    function (Event $callbackEvent) use ($callback) {
                        $environment = $callbackEvent->getEnvironment();
                        $model       = $callbackEvent->getModel();

                        $dc = new DcCompat($environment, $this->parseTimeStampProperties($model));

                        System::importStatic($callback[0])->{$callback[1]}($dc);
                    }
                );
            }

            unset($GLOBALS['TL_DCA'][$dataProvider]['config'][$callbackName]);
        }
    }

    /**
     * convert property for time from string to time.
     *
     * @param DefaultModel $model
     *
     * @return DefaultModel
     */
    protected function parseTimeStampProperties(DefaultModel $model)
    {
        $timeStampProperties = array('date');

        foreach ($timeStampProperties as $timeStampProperty) {
            if (!$model->getProperty($timeStampProperty)) {
                continue;
            }

            switch ($timeStampProperty) {
                case 'date':
                    $date = strtotime($model->getProperty('date') . ' ' . $model->getProperty('time'));
                    $model->setProperty('date', $date);
                    $model->setProperty('time', $date);

                    break;

                default:
            }
        }

        return $model;
    }
}
