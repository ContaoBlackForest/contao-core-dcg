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

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreEditModelEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class FormFieldController
 *
 * @package ContaoBlackForest\Contao\Core\DcGeneral\DataContainer
 */
class FormFieldController implements EventSubscriberInterface
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
            PreEditModelEvent::NAME => array(
                array('handlePossiblePaletteNameFromSelectorValue')
            ),
            BuildWidgetEvent::NAME  => array(
                array('ResetPossiblePaletteNameFromSelectorValue')
            )
        );
    }

    /**
     * Handle possible palette names from the selector values.
     *
     * @param PreEditModelEvent $event The event.
     *
     * Fixme If declared as feature for dcg?
     *
     * @see https://github.com/contao/core/blob/master/system/modules/core/drivers/DC_Table.php#L3089
     */
    public function handlePossiblePaletteNameFromSelectorValue(PreEditModelEvent $event)
    {
        if (('tl_form_field' !== $event->getEnvironment()->getDataDefinition()->getName())
            || (0 !== strpos($event->getEnvironment()->getInputProvider()->getValue('type'), 'fieldset'))
        ) {
            return;
        }

        $model     = $event->getModel();
        $typeValue = $event->getEnvironment()->getInputProvider()->getValue('fsType') ?: $model->getProperty('fsType');

        $event->getEnvironment()->getInputProvider()->setValue(
            'type',
            $event->getEnvironment()->getInputProvider()->getValue('type') . $typeValue
        );
    }

    /**
     * Reset the type property.
     *
     * @param BuildWidgetEvent $event The event.
     */
    public function ResetPossiblePaletteNameFromSelectorValue(BuildWidgetEvent $event)
    {
        if (('tl_form_field' !== $event->getEnvironment()->getDataDefinition()->getName())
            || ('type' !== $event->getProperty()->getName())
            || (0 !== strpos($event->getModel()->getProperty('type'), 'fieldset'))
            || (0 !== strpos($event->getWidget()->value, 'fieldset'))
        ) {
            return;
        }

        $event->getModel()->setProperty('type', 'fieldset');
        $event->getWidget()->value = 'fieldset';
    }
}
