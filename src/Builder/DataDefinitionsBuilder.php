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


use ContaoBlackForest\Contao\Core\DcGeneral\Service\TableToGeneralService;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinition;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ToggleCommand;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ToggleCommandInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
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
                array('setListLabelConfig', 202),
                array('disableVersions', 201),
                array('setIdParamToOperation', 200),
                array('setParentTablePropertyToDcaConfig', 200),
                array('parseModelCommands'),
            ),

            DcGeneralEvents::ACTION => array(
                array('toggleOperationButton', 200),
                array('validateParentHeaderInformation', 200),
                array('inverseOperationButton', 200)
            ),
        );
    }

    /**
     * Set the data provider to the dca_config
     *
     * @param BuildDataDefinitionEvent $event
     */
    public function setDataProvider(BuildDataDefinitionEvent $event)
    {
        global $container;

        /** @var TableToGeneralService $service */
        $service = $container['dc-general.table_to_general'];

        $containerName = $event->getContainer()->getName();

        if (!$controller = $service->getDataProviderController($containerName)) {
            return;
        }

        $providers = $controller->getPermittedDataProvider();

        $dataProviderConfig = array();
        foreach ($providers as $index => $provider) {
            if ($provider !== $containerName
                && (!isset($dataProviderConfig['default'])
                    && !isset($dataProviderConfig['parent']))
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
                unset($providers[$index - 1]);
            }
        }

        if (!empty($providers)) {
            foreach ($providers as $index => $provider) {
                $dataProviderConfig[$provider] = array('source' => $provider);
            }
        }

        $GLOBALS['TL_DCA'][$containerName]['dca_config']['data_provider'] = $dataProviderConfig;
    }

    /**
     * Set the child condition to the dca_config
     *
     * @param BuildDataDefinitionEvent $event
     */
    public function setChildCondition(BuildDataDefinitionEvent $event)
    {
        global $container;

        /** @var TableToGeneralService $service */
        $service = $container['dc-general.table_to_general'];

        $containerName = $event->getContainer()->getName();

        if (!$controller = $service->getDataProviderController($containerName)) {
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
            foreach ($reverseProviders as $reverseIndex => $reverseProvider) {
                if ($reverseIndex + 1 === count($reverseProviders)) {
                    break;
                }

                $childCondition[] = $this->getChildCondition($reverseProviders[$reverseIndex + 1], $reverseProvider);
            }
        }

        $GLOBALS['TL_DCA'][$containerName]['dca_config']['childCondition'] = $childCondition;
    }


    /**
     * Set the list label config
     *
     * @param BuildDataDefinitionEvent $event
     */
    public function setListLabelConfig(BuildDataDefinitionEvent $event)
    {
        global $container;

        /** @var TableToGeneralService $service */
        $service = $container['dc-general.table_to_general'];

        $containerName = $event->getContainer()->getName();

        if (!$controller = $service->getDataProviderController($containerName)) {
            return;
        }

        $listLabelConfig = $controller->getListLabelConfig();
        if (!isset($listLabelConfig[$containerName])) {
            return;
        }

        $GLOBALS['TL_DCA'][$containerName]['list']['label'] = $listLabelConfig[$containerName];
    }

    /**
     * Disable versions, this is missing feature from DC General
     *
     * @param BuildDataDefinitionEvent $event
     *
     * Fixme by DC General. DC General don´t can handle Versions.
     */
    public function disableVersions(BuildDataDefinitionEvent $event)
    {
        global $container;

        /** @var TableToGeneralService $service */
        $service = $container['dc-general.table_to_general'];

        $containerName = $event->getContainer()->getName();

        if (!$controller = $service->getDataProviderController($containerName)) {
            return;
        }

        $GLOBALS['TL_DCA'][$containerName]['config']['enableVersioning'] = false;
    }

    /**
     * Set the id parameter to operation, if the operation go to a child table
     *
     * @param BuildDataDefinitionEvent $event
     */
    public function setIdParamToOperation(BuildDataDefinitionEvent $event)
    {
        global $container;

        /** @var TableToGeneralService $service */
        $service = $container['dc-general.table_to_general'];

        $containerName = $event->getContainer()->getName();

        if (!$controller = $service->getDataProviderController($containerName)) {
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
     * Set the the parent table property to the dca config.
     *
     * @param BuildDataDefinitionEvent $event
     */
    public function setParentTablePropertyToDcaConfig(BuildDataDefinitionEvent $event)
    {
        global $container;

        /** @var TableToGeneralService $service */
        $service = $container['dc-general.table_to_general'];

        $containerName = $event->getContainer()->getName();

        if (!$controller = $service->getDataProviderController($containerName)) {
            return;
        }

        if (!isset($GLOBALS['TL_DCA'][$containerName]['fields']['ptable'])) {
            return;
        }

        $GLOBALS['TL_DCA'][$containerName]['dca_config']['parent_table_property'] = 'ptable';
    }

    /**
     * Parse model commands for edit and editheader.
     * Give in parent list view the header edit button the right action edit.
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     *
     * TODO remove this if dc general handle it.
     */
    public function parseModelCommands(BuildDataDefinitionEvent $event)
    {
        /** @var TableToGeneralService $service */
        $service = $GLOBALS['container']['dc-general.table_to_general'];

        $container = $event->getContainer();

        if (!$controller = $service->getDataProviderController($container->getName())) {
            return;
        }

        $backendView = $container->getDefinition(Contao2BackendViewDefinitionInterface::NAME);

        $modelCommands = $backendView->getModelCommands();
        if (false === $modelCommands->hasCommandNamed('editheader')) {
            return;
        }

        $editChildes = $modelCommands->getCommandNamed('edit');
        $editChildes->setName('editChildes');

        $editChildes = $modelCommands->getCommandNamed('editheader');
        $editChildes->setName('edit');
    }

    /**
     * Validate the header properties, if exists in parent property definition.
     * This is useful for dynamic table e.g. tl_content.
     *
     * @param ActionEvent $event
     *
     * Fixme by DC General. Validate this by DC General.
     */
    public function validateParentHeaderInformation(ActionEvent $event)
    {
        global $container;

        /** @var TableToGeneralService $service */
        $service = $container['dc-general.table_to_general'];

        $environment        = $event->getEnvironment();
        $dataDefinition     = $environment->getDataDefinition();
        $dataDefinitionName = $dataDefinition->getName();

        if (!$controller = $service->getDataProviderController($dataDefinitionName)) {
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
     * Toggle operation button
     *
     * @param ActionEvent $event
     */
    public function toggleOperationButton(ActionEvent $event)
    {
        global $container;

        /** @var TableToGeneralService $service */
        $service = $container['dc-general.table_to_general'];

        $environment        = $event->getEnvironment();
        $dataDefinition     = $environment->getDataDefinition();
        $dataDefinitionName = $dataDefinition->getName();

        if (!$controller = $service->getDataProviderController($dataDefinitionName)) {
            return;
        }

        $toggleOperation = $controller->getToggleOperation();
        if (!array_key_exists($dataDefinitionName, $toggleOperation)) {
            return;
        }

        /** @var Contao2BackendViewDefinition $view */
        $view          = $dataDefinition->getDefinition('view.contao2backend');
        $modelCommands = $view->getModelCommands();

        $replaceCommands = array();
        /** @var ToggleCommandInterface $command */
        foreach ($modelCommands->getCommands() as $command) {
            if (!array_key_exists($command->getName(), $toggleOperation[$dataDefinitionName])) {
                continue;
            }

            $noToggleCommand = null;
            if (!$command instanceof ToggleCommandInterface) {
                $noToggleCommand = $command;

                $command = new ToggleCommand();
                $command->setName($noToggleCommand->getName());
                $command->setParameters($noToggleCommand->getParameters());
                $command->setLabel($noToggleCommand->getLabel());
                $command->setDescription($noToggleCommand->getDescription());
                $command->setExtra($noToggleCommand->getExtra());
            }

            $extra = $command->getExtra()->getArrayCopy();

            if (array_key_exists('attributes', $extra)) {
                unset($extra['attributes']);
            }

            if (isset($toggleOperation[$dataDefinitionName][$command->getName()]['icon_disabled'])) {
                $extra['icon_disabled'] = $toggleOperation[$dataDefinitionName][$command->getName()]['icon_disabled'];
            }

            $command->setExtra(new \ArrayObject($extra));
            $command->setToggleProperty(
                $toggleOperation[$dataDefinitionName][$command->getName()]['property']
            );

            if ($noToggleCommand) {
                $replaceCommands[] = array(
                    'replace' => $noToggleCommand,
                    'with'    => $command
                );
            }
        }

        if (!empty($replaceCommands)) {
            $commands = $modelCommands->getCommands();

            foreach ($commands as $hash => $command) {
                foreach ($replaceCommands as $replaceCommand) {
                    if ($replaceCommand['replace'] !== $command) {
                        continue;
                    }

                    $commands[$hash] = $replaceCommand['with'];
                }
            }

            $modelCommands->setCommands($commands);
        }
    }

    /**
     * Inverse toggle operation button
     *
     * @param ActionEvent $event
     */
    public function inverseOperationButton(ActionEvent $event)
    {
        global $container;

        /** @var TableToGeneralService $service */
        $service = $container['dc-general.table_to_general'];

        $environment        = $event->getEnvironment();
        $dataDefinition     = $environment->getDataDefinition();
        $dataDefinitionName = $dataDefinition->getName();

        if (!$controller = $service->getDataProviderController($dataDefinitionName)) {
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
