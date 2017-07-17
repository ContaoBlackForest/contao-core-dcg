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

namespace ContaoBlackForest\Contao\Core\DcGeneral\DataContainer;

use Contao\System;
use ContaoBlackForest\Contao\Core\DcGeneral\Service\TableToGeneralService;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultModel;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultProperty;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDuplicateModelEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Class ModelController
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral\DataContainer
 */
class ModelController implements EventSubscriberInterface
{

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            PostPersistModelEvent::NAME => array(
                array('handlePostPersistModel')
            ),

            PreDuplicateModelEvent::NAME => array(
                array('handleDuplicate')
            ),

            PostDuplicateModelEvent::NAME => array(
                array('handleDuplicateAlias')
            )
        );
    }

    /**
     * handle post persist model.
     *
     * @param PostPersistModelEvent $event
     * @param                       $eventName
     * @param EventDispatcher       $dispatcher
     *
     * Fixme by Dc General. property save callbacks are execute too early.
     */
    public function handlePostPersistModel(PostPersistModelEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        global $container;

        /** @var TableToGeneralService $service */
        $service = $container['dc-general.table_to_general'];

        $environment        = $event->getEnvironment();
        $dataDefinition     = $environment->getDataDefinition();
        $dataDefinitionName = $dataDefinition->getName();

        if (!$controller = $service->getDataProviderController($dataDefinitionName)
        ) {
            return;
        }

        $model = $event->getModel();

        $properties = $dataDefinition->getPropertiesDefinition()->getProperties();
        foreach ($properties as $property) {
            switch ($property->getName()) {
                case 'alias':
                    if ($event->getOriginalModel()->getProperty($property->getName())) {
                        break;
                    }

                    $model->setProperty($property->getName(), '');

                    break;

                default:
            }

            $this->executeSaveCallback($model, $property, $environment);
        }

        $provider = $environment->getDataProvider($model->getProviderName());
        $provider->save($model);
    }

    /**
     * handle duplicate model.
     * Properties id, tstamp are excluded. The property published set to 0.
     *
     * @param PreDuplicateModelEvent $event
     * @param                        $eventName
     * @param EventDispatcher        $dispatcher
     *
     * Fixme by DC General. If duplicate a model, properties don´t has defaultValue and execute save callbacks too early.
     */
    public function handleDuplicate(PreDuplicateModelEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        global $container;

        /** @var TableToGeneralService $service */
        $service = $container['dc-general.table_to_general'];

        $environment        = $event->getEnvironment();
        $inputProvider      = $environment->getInputProvider();
        $dataDefinition     = $environment->getDataDefinition();
        $dataDefinitionName = $dataDefinition->getName();

        if ($inputProvider->getParameter('act') !== 'copy'
            || !$controller = $service->getDataProviderController($dataDefinitionName)
        ) {
            return;
        }

        $model = $event->getModel();

        $properties = $dataDefinition->getPropertiesDefinition()->getProperties();
        foreach ($properties as $property) {
            if ($model->getProperty($property->getName())
                || in_array($property->getName(), array('id', 'tstamp'))
            ) {
                continue;
            }

            $extra = $property->getExtra();
            if ($extra
                && array_key_exists('doNotCopy', $extra)
                && $extra['doNotCopy']
            ) {
                if (!$defaultValue = $property->getDefaultValue()) {
                    switch ($property->getWidgetType()) {
                        case 'checkbox':
                            $defaultValue = 0;

                            break;
                        default:
                    }
                }

                $model->setProperty($property->getName(), $defaultValue);
            }

            $this->executeSaveCallback($model, $property, $environment);
        }
    }

    /**
     * After duplicate a model with property alias, reset the property and execute the save callback.
     *
     * @param PostDuplicateModelEvent $event
     * @param                         $eventName
     * @param EventDispatcher         $dispatcher
     *
     * Fixme by Dc General. While save callbacks execute too early, must be execute this method.
     */
    public function handleDuplicateAlias(PostDuplicateModelEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        global $container;

        /** @var TableToGeneralService $service */
        $service = $container['dc-general.table_to_general'];

        $environment          = $event->getEnvironment();
        $inputProvider        = $environment->getInputProvider();
        $dataDefinition       = $environment->getDataDefinition();
        $dataDefinitionName   = $dataDefinition->getName();
        $propertiesDefinition = $dataDefinition->getPropertiesDefinition();

        if (!$propertiesDefinition->hasProperty('alias')
            || $inputProvider->getParameter('act') !== 'copy'
            || !$controller = $service->getDataProviderController($dataDefinitionName)
        ) {
            return;
        }

        $model    = $event->getModel();
        $property = $propertiesDefinition->getProperty('alias');

        $model->setProperty($property->getName(), '');

        $this->executeSaveCallback($model, $property, $environment);

        $provider = $environment->getDataProvider($model->getProviderName());
        $provider->save($model);
    }

    /**
     * Executed the save callback, if callback defined for this property.
     *
     * @param DefaultModel         $model
     * @param DefaultProperty      $property
     * @param EnvironmentInterface $environment
     */
    protected function executeSaveCallback(DefaultModel $model, DefaultProperty $property, EnvironmentInterface $environment)
    {
        if (!isset($GLOBALS['TL_DCA'][$model->getProviderName()]['fields'][$property->getName()]['save_callback'])) {
            return;
        }

        $propertyValue = $model->getProperty($property->getName());

        $dc = new DcCompat($environment, $model, $property->getName());

        $saveCallback = $GLOBALS['TL_DCA'][$model->getProviderName()]['fields'][$property->getName()]['save_callback'];
        foreach ($saveCallback as $callback) {
            $propertyValue = System::importStatic($callback[0])->{$callback[1]}($propertyValue, $dc);
        }

        $model->setProperty($property->getName(), $propertyValue);
    }
}
