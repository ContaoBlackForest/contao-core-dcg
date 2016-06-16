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
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ToggleCommandInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class DataDefinitionsBuilder
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral\Builds
 */
class ActionController implements EventSubscriberInterface
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
            DcGeneralEvents::ACTION => array(
                array('validateInverseToggleOperation', 200)
            )
        );
    }

    /**
     * Validate the inverse toggle operation by action toggle.
     *
     * @param ActionEvent     $event
     * @param                 $eventName
     * @param EventDispatcher $dispatcher
     */
    public function validateInverseToggleOperation(ActionEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        if ($event->getAction()->getName() !== 'toggle') {
            return;
        }

        $environment        = $event->getEnvironment();
        $dataDefinition     = $environment->getDataDefinition();
        $dataDefinitionName = $dataDefinition->getName();

        if (!$controller = $this->getDataProviderController($dataDefinitionName)) {
            return;
        }

        $inverseToggleOperation = $controller->getInverseToggleOperation();
        if (!array_key_exists($dataDefinitionName, $inverseToggleOperation)) {
            return;
        }

        /** @var Contao2BackendViewDefinition $view */
        $view          = $dataDefinition->getDefinition('view.contao2backend');
        $modelCommands = $view->getModelCommands();

        /** @var ToggleCommandInterface $command */
        foreach ($modelCommands->getCommands() as $command) {
            if (!array_key_exists($command->getName(), $inverseToggleOperation[$dataDefinitionName])) {
                continue;
            }

            $extra = $command->getExtra()->getArrayCopy();

            if (array_key_exists('attributes', $extra)) {
                unset($extra['attributes']);
            }

            $command->setExtra(new \ArrayObject($extra));
            $command->setToggleProperty(
                $inverseToggleOperation[$dataDefinitionName][$command->getName()]['property']
            );
            $command->setInverse(true);
        }
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
