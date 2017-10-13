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

use ContaoBlackForest\Contao\Core\DcGeneral\Service\TableToGeneralService;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultProperty;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Class MemberController
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral\DataContainer
 */
class MemberController implements EventSubscriberInterface
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
                array('addPropertyForTableHead'),
                array('disableGrouping')
            )
        );
    }

    /**
     * Add property for the table head.
     *
     * @param BuildDataDefinitionEvent $event
     *
     * @®return void
     */
    public function addPropertyForTableHead(BuildDataDefinitionEvent $event)
    {
        global $container;

        /** @var TableToGeneralService $service */
        $service = $container['dc-general.table_to_general'];

        if (!$controller = $service->getDataProviderController('tl_member')) {
            return;
        }

        $properties = $event->getContainer()->getPropertiesDefinition();

        $dummyProperty = new DefaultProperty('icon');

        $properties->addProperty($dummyProperty);
    }

    /**
     * Add property for the table head.
     *
     * @param BuildDataDefinitionEvent $event
     *
     * @®return void
     */
    public function disableGrouping(BuildDataDefinitionEvent $event)
    {
        global $container;

        /** @var TableToGeneralService $service */
        $service = $container['dc-general.table_to_general'];

        if (!$controller = $service->getDataProviderController('tl_member')) {
            return;
        }

        $backendView            = $event->getContainer()->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        $groupSortingCollection = $backendView->getListingConfig()->getGroupAndSortingDefinition();
        $iteratorCollection     = $groupSortingCollection->getIterator();

        foreach ($iteratorCollection->getArrayCopy() as $groupAndSortingDefinition) {
            $iteratorDefinition = $groupAndSortingDefinition->getIterator();

            foreach ($iteratorDefinition->getArrayCopy() as $groupAndSortingInformation) {
                $groupAndSortingInformation->setGroupingMode(GroupAndSortingInformationInterface::GROUP_NONE);
            }
        }
    }
}
