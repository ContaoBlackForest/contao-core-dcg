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

use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class FormFieldController
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral\DataContainer
 */
class ModuleController implements EventSubscriberInterface
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
                array('removeUnknownPropertyInPalette', 1)
            )
        );
    }

    /**
     * Remove unknown property in palette. (eg. com_protected)
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     *
     * Fixme what is com_protected
     */
    public function removeUnknownPropertyInPalette(BuildDataDefinitionEvent $event)
    {
        if ('tl_module' !== $event->getContainer()->getName()) {
            return;
        }

        $container          = $event->getContainer();
        $properties         = $container->getPropertiesDefinition();
        $palettesDefinition = $container->getPalettesDefinition();
        $palettes           = $palettesDefinition->getPalettes();

        foreach ($palettes as $palette) {
            foreach ($palette->getProperties() as $property) {
                if ($properties->hasProperty($name = $property->getName())) {
                    continue;
                }

                foreach ($palette->getLegends() as $legend) {
                    if (!$legend->hasProperty($name)) {
                        continue;
                    }

                    $legend->removeProperty($legend->getProperty($name));
                }
            }
        }
    }
}
