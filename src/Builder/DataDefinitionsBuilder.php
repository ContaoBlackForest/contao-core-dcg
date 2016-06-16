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

namespace ContaoBlackForest\Contao\Core\DcGeneral\Builder;


use ContaoBlackForest\Contao\Core\DcGeneral\AbstractController;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ToggleCommandInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\Event\ViewEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class DataDefinitionsBuilder
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral\Builds
 */
class DataDefinitionsBuilder implements EventSubscriberInterface
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
            BuildDataDefinitionEvent::NAME => array(
                array('setDataProvider', 203),
                array('setChildCondition', 202),
                array('disableVersions', 201),
                array('unsetParentTable', 200),
                array('setIdParamToOperation', 200),
            ),

            DcGeneralEvents::VIEW => array(
                array('validateParentHeaderInformation', 200),
                array('inverseOperationButton', 200)
            ),
        );
    }

    /**
     * Set the data provider to the dca_config
     *
     * @param BuildDataDefinitionEvent $event
     * @param                          $eventName
     * @param EventDispatcher          $dispatcher
     */
    public function setDataProvider(BuildDataDefinitionEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        $containerName = $event->getContainer()->getName();

        if (!$controller = $this->getDataProviderController($containerName)) {
            return;
        }

        $providers = $controller->getPermittedDataProvider();

        $dataProviderConfig = array();
        foreach ($providers as $index => $provider) {
            if ($provider !== $containerName
                && (!isset($dataProviderConfig['default'])
                    || !isset($dataProviderConfig['parent']))
            ) {
                continue;
            }

            $providerSection = $provider;

            if ($provider === $containerName
                && $index === 0
            ) {
                $providerSection = 'default';
            }

            $dataProviderConfig[$providerSection] = array('source' => $provider);

            if ($provider === $containerName
                && isset($providers[$index - 1])
            ) {
                $dataProviderConfig['parent'] = array('source' => $providers[$index - 1]);
            }
        }

        $GLOBALS['TL_DCA'][$containerName]['dca_config']['data_provider'] = $dataProviderConfig;
    }

    /**
     * Set the child condition to the dca_config
     *
     * @param BuildDataDefinitionEvent $event
     * @param                          $eventName
     * @param EventDispatcher          $dispatcher
     */
    public function setChildCondition(BuildDataDefinitionEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        $containerName = $event->getContainer()->getName();

        if (!$controller = $this->getDataProviderController($containerName)) {
            return;
        }

        $providers = $controller->getPermittedDataProvider();

        $childCondition = array();
        foreach ($providers as $index => $provider) {
            if (($provider !== $containerName
                 && empty($childCondition))
                || !isset($providers[$index + 1])
            ) {
                continue;
            }

            $childCondition[] = $this->getChildCondition($provider, $providers[$index + 1]);

            if ($provider === $containerName
                && isset($GLOBALS['TL_DCA'][$containerName]['dca_config']['data_provider']['parent'])
            ) {
                $childCondition[] = $this->getChildCondition(
                    $GLOBALS['TL_DCA'][$containerName]['dca_config']['data_provider']['parent']['source'],
                    $provider
                );
            }
        }

        $reverseProviders = array_reverse($providers);
        if (empty($childCondition)
            && $reverseProviders[0] === $containerName
        ) {
            $condition = $this->getChildCondition($reverseProviders[1], $reverseProviders[0]);

            $condition['filter'][] = array(
                'local'        => 'ptable',
                'remote_value' => $reverseProviders[1],
                'operation'    => '='
            );

            $childCondition[] = $condition;
        }

        $GLOBALS['TL_DCA'][$containerName]['dca_config']['childCondition'] = $childCondition;
    }

    /**
     * Disable versions, this is missing feature from DC General
     *
     * @param BuildDataDefinitionEvent $event
     * @param                          $eventName
     * @param EventDispatcher          $dispatcher
     */
    public function disableVersions(BuildDataDefinitionEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        $containerName = $event->getContainer()->getName();

        if (!$controller = $this->getDataProviderController($containerName)) {
            return;
        }

        $GLOBALS['TL_DCA'][$containerName]['config']['enableVersioning'] = false;
    }

    /**
     * Set the id parameter to operation, if the operation go to a child table
     *
     * @param BuildDataDefinitionEvent $event
     * @param                          $eventName
     * @param EventDispatcher          $dispatcher
     */
    public function setIdParamToOperation(BuildDataDefinitionEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        $containerName = $event->getContainer()->getName();

        if (!$controller = $this->getDataProviderController($containerName)) {
            return;
        }

        foreach ($GLOBALS['TL_DCA'][$containerName]['list']['operations'] as &$operation) {
            if (!isset($operation['href']) || !stristr($operation['href'], 'table=')) {
                continue;
            }

            $operation['idparam'] = 'pid';
        }
    }

    /**
     * Unset the parent config. It conflict with breadcrumb.
     *
     * @see \Contao\Backend::548
     *
     * @param BuildDataDefinitionEvent $event
     * @param                          $eventName
     * @param EventDispatcher          $dispatcher
     */
    public function unsetParentTable(BuildDataDefinitionEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        $containerName = $event->getContainer()->getName();

        if (!$controller = $this->getDataProviderController($containerName)) {
            return;
        }

        if (isset($GLOBALS['TL_DCA'][$containerName]['config']['ptable'])) {
            unset($GLOBALS['TL_DCA'][$containerName]['config']['ptable']);
        }
    }

    /**
     * Validate the header properties, if exists in parent property definition.
     * This is useful for dynamic table e.g. tl_content.
     *
     * @param DcGeneralEvents|ViewEvent $event
     * @param                           $eventName
     * @param EventDispatcher           $dispatcher
     */
    public function validateParentHeaderInformation(ViewEvent $event, $eventName, EventDispatcher $dispatcher)
    {
        $environment        = $event->getEnvironment();
        $dataDefinition     = $environment->getDataDefinition();
        $dataDefinitionName = $dataDefinition->getName();

        if (!$controller = $this->getDataProviderController($dataDefinitionName)) {
            return;
        }

        /** @var Contao2BackendViewDefinition $view */
        $view          = $dataDefinition->getDefinition('view.contao2backend');
        $listingConfig = $view->getListingConfig();

        if (!$headerProperties = $listingConfig->getHeaderPropertyNames()) {
            return;
        }

        $parentDataDefinition       = $environment->getParentDataDefinition();
        $parentPropertiesDefinition = $parentDataDefinition->getPropertiesDefinition();
        $parentProperties           = $parentPropertiesDefinition->getPropertyNames();

        $validateHeaderProperties = array();
        foreach ($headerProperties as $headerProperty) {
            if (!in_array($headerProperty, $parentProperties)) {
                continue;
            }

            $validateHeaderProperties[] = $headerProperty;
        }

        $listingConfig->setHeaderPropertyNames($validateHeaderProperties);
    }

    /**
     * Inverse toggle operation button
     *
     * @param DcGeneralEvents|ViewEvent $event
     * @param                           $eventName
     * @param EventDispatcher           $dispatcher
     */
    public function inverseOperationButton(ViewEvent $event, $eventName, EventDispatcher $dispatcher)
    {
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

    /**
     * Get a single child condition
     *
     * @param $from
     * @param $to
     *
     * @return array
     */
    protected function getChildCondition($from, $to)
    {
        return array
        (
            'from'    => $from,
            'to'      => $to,
            'setOn'   => array
            (
                array
                (
                    'to_field'   => 'pid',
                    'from_field' => 'id',
                ),
            ),
            'filter'  => array
            (
                array
                (
                    'local'     => 'pid',
                    'remote'    => 'id',
                    'operation' => '=',
                ),
            ),
            'inverse' => array
            (
                array
                (
                    'local'     => 'pid',
                    'remote'    => 'id',
                    'operation' => '=',
                ),
            )
        );
    }
}
