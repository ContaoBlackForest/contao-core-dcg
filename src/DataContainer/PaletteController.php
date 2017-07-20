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
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultProperty;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Legend;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Palette;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Class PaletteController
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral\DataContainer
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
            DcGeneralEvents::ACTION => array(
                #array('handleSubSelector')
            )
        );
    }

    /**
     * This add missing sub selectors
     *
     * @param ActionEvent     $event
     *
     * Fixme since DC General beta 39 this don´t work.
     * Fixme by DC General. DC General don´t add all sub selector properties to the legend.
     */
    public function handleSubSelector(ActionEvent $event)
    {
        global $container;

        /** @var TableToGeneralService $service */
        $service = $container['dc-general.table_to_general'];

        $environment        = $event->getEnvironment();
        $dataDefinition     = $environment->getDataDefinition();
        $dataDefinitionName = $dataDefinition->getName();
        $inputProvider      = $environment->getInputProvider();

        if (!$inputProvider->hasParameter('id')
            || $event->getAction()->getName() !== 'edit'
            || !$controller = $service->getDataProviderController($dataDefinitionName)
        ) {
            return;
        }

        $properties         = $dataDefinition->getPropertiesDefinition()->getProperties();
        $__selector__       = $GLOBALS['TL_DCA'][$dataDefinitionName]['palettes']['__selector__'];
        $selectorProperties = array_intersect_key($properties, array_flip($__selector__));

        $dataProvider = $environment->getDataProvider();
        $modelId      = ModelId::fromSerialized($inputProvider->getParameter('id'));
        $model        = $dataProvider->fetch($dataProvider->getEmptyConfig()->setId($modelId->getId()));

        foreach ($selectorProperties as $property) {
            $propertyName = $property->getName();

            if (!in_array($propertyName, $__selector__)
            ) {
                continue;
            }

            if (!$options = $property->getOptions()) {
                if (!$options = $this->getOptionsOptionsCallback($property, $dataDefinitionName, $environment, $model)) {
                    continue;
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
    }

    /**
     * Get property options form options callback
     *
     * @param DefaultProperty      $property
     * @param                      $dataProvider
     * @param EnvironmentInterface $environment
     * @param DefaultModel         $model
     *
     * @return null
     */
    protected function getOptionsOptionsCallback(DefaultProperty $property, $dataProvider, EnvironmentInterface $environment, DefaultModel $model)
    {
        $dc = new DcCompat($environment, $model, $property->getName());

        $propertyField = $GLOBALS['TL_DCA'][$dataProvider]['fields'][$property->getName()];

        if (!array_key_exists('options_callback', $propertyField)) {
            return null;
        }

        $callback = $propertyField['options_callback'];

        return System::importStatic($callback[0])->{$callback[1]}($dc);
    }
}
