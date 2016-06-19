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

use Contao\System;
use Contao\Widget;
use ContaoBlackForest\Contao\Core\DcGeneral\Service\TableToGeneralService;
use ContaoCommunityAlliance\DcGeneral\Contao\Compatibility\DcCompat;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Legend;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Palette;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Class PaletteController
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral\Palette
 */
class PaletteController implements EventSubscriberInterface
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
            ManipulateWidgetEvent::NAME => array(
                array('handleSubSelector', -100)
            )
        );
    }

    /**
     * This add missing sub selectors
     *
     * @param ManipulateWidgetEvent $event
     * @param                       $eventName
     * @param EventDispatcher       $dispatcher
     * 
     * Fixme by DC General. DC General don´t add all sub selector properties to the legend. 
     */
    public function handleSubSelector(ManipulateWidgetEvent $event, $eventName, EventDispatcher $dispatcher)
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

        $property     = $event->getProperty();
        $propertyName = $property->getName();

        $__selector__ = $GLOBALS['TL_DCA'][$dataDefinitionName]['palettes']['__selector__'];
        if (!in_array($propertyName, $__selector__)
        ) {
            return;
        }

        if (!$options = $property->getOptions()) {
            if (!$options = $this->getOptionsFromWidget($event->getWidget())) {
                return;
            }
        }

        $subPalettes = $GLOBALS['TL_DCA'][$dataDefinitionName]['subpalettes'];

        $palettes = $dataDefinition->getPalettesDefinition()->getPalettes();
        /** @var Palette $palette */
        foreach ($palettes as $palette) {
            if (!$palette->getProperty($propertyName)) {
                continue;
            }

            /** @var Legend $legend */
            foreach ($palette->getLegends() as $legend) {
                $addLegendProperties = array();

                if (!$legend->hasProperty($propertyName)) {
                    continue;
                }

                foreach ($options as $option) {
                    $subSelectorValue = $propertyName . '_' . $option;
                    if (!array_key_exists($subSelectorValue, $subPalettes)) {
                        continue;
                    }

                    $subPaletteProperties = explode(',', $subPalettes[$subSelectorValue]);
                    foreach ($subPaletteProperties as $subPaletteProperty) {
                        if ($legend->hasProperty($subPaletteProperty)) {
                            $legendProperty = $legend->getProperty($subPaletteProperty);

                            $visibleCondition = $legendProperty->getVisibleCondition();
                            if ($visibleCondition->getPropertyName() === $propertyName
                                && $visibleCondition->getPropertyValue() === $option
                            ) {
                                continue;
                            }
                        }


                        $visibleCondition = new PropertyValueCondition();
                        $visibleCondition->setPropertyName($propertyName);
                        $visibleCondition->setPropertyValue($option);

                        $paletteProperty = new Property($subPaletteProperty);
                        $paletteProperty->setVisibleCondition($visibleCondition);

                        $addLegendProperties[] = $paletteProperty;
                    }
                }

                if (empty($addLegendProperties)) {
                    continue;
                }

                $legendProperties = array_merge($legend->getProperties(), $addLegendProperties);
                $legend->clearProperties()->setProperties($legendProperties);
            }
        }
    }

    /**
     * Get widget options form options callback
     *
     * @param Widget $widget
     *
     * @return null
     */
    protected function getOptionsFromWidget(Widget $widget)
    {
        /** @var DcCompat $dataContainer */
        $dataContainer = $widget->dataContainer;

        $property = $GLOBALS['TL_DCA'][$dataContainer->getModel()->getProviderName()]['fields'][$dataContainer->getPropertyName()];

        if (!array_key_exists('options_callback', $property)) {
            return null;
        }

        $callback = $property['options_callback'];

        return System::importStatic($callback[0])->{$callback[1]}($widget->dataContainer);
    }
}
