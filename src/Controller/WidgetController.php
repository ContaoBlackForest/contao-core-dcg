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

namespace ContaoBlackForest\Contao\Core\DcGeneral\Controller;

use ContaoBlackForest\Contao\Core\DcGeneral\AbstractController;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultDataProvider;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultModel;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Class WidgetController
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral\Controller
 */
class WidgetController implements EventSubscriberInterface
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
            GetPropertyOptionsEvent::NAME => array(
                array('handleSelectForeignKey')
            )
        );
    }

    /**
     * Handle the foreign key for select widget
     *
     * @param GetPropertyOptionsEvent $event
     * @param                         $eventName
     * @param EventDispatcher         $dispatcher
     * 
     * Fixme by DC General. This is missing by DC General.
     */
    public function handleSelectForeignKey(GetPropertyOptionsEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        $environment        = $event->getEnvironment();
        $dataDefinition     = $environment->getDataDefinition();
        $dataDefinitionName = $dataDefinition->getName();

        if (!$controller = $this->getDataProviderController($dataDefinitionName)) {
            return;
        }

        $property = $dataDefinition->getPropertiesDefinition()->getProperty($event->getPropertyName());
        if ($property->getWidgetType() !== 'select'
            || $event->getOptions()
            || !array_key_exists(
                'foreignKey',
                $GLOBALS['TL_DCA'][$dataDefinitionName]['fields'][$event->getPropertyName()]
            )
        ) {
            return;
        }

        $foreignKey = explode(
            '.',
            $GLOBALS['TL_DCA'][$dataDefinitionName]['fields'][$event->getPropertyName()]['foreignKey']
        );

        $foreignKeyProvider = new DefaultDataProvider();
        $foreignKeyProvider->setBaseConfig(
            array(
                'source' => $foreignKey[0],
            )
        );
        $foreignKeyModel = $foreignKeyProvider->fetchAll($foreignKeyProvider->getEmptyConfig()->setSorting(array($foreignKey[1])));

        /** @var \ArrayIterator $foreignKeyIterator */
        $foreignKeyIterator = $foreignKeyModel->getIterator();

        $options = array();
        while ($foreignKeyIterator->current()) {
            /** @var DefaultModel $current */
            $current = $foreignKeyIterator->current();

            $options[$current->getID()] = $current->getProperty($foreignKey[1]);

            $foreignKeyIterator->next();
        }

        $event->setOptions($options);
    }

    /**
     * Get the data provider controller from service container
     *
     * @param $containerName
     *
     * @return AbstractController|null
     */
    protected function getDataProviderController($containerName)
    {
        global $container;

        $serviceName = 'dc-general.table_to_general.' . $containerName;
        if (!isset($container[$serviceName])) {
            return null;
        }

        /** @var AbstractController $controller */
        $controller = $container[$serviceName];

        return $controller;
    }
}
