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

$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('ContaoBlackForest\Contao\Core\DcGeneral\Service\NewsArchiveService', 'initialize');
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('ContaoBlackForest\Contao\Core\DcGeneral\Service\NewsFeedService', 'initialize');

$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('ContaoBlackForest\Contao\Core\DcGeneral\Service\FaqService', 'initialize');

$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('ContaoBlackForest\Contao\Core\DcGeneral\Service\NewsletterService', 'initialize');
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('ContaoBlackForest\Contao\Core\DcGeneral\Service\NewsletterRecipientsService', 'initialize');

$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('ContaoBlackForest\Contao\Core\DcGeneral\Service\CalendarService', 'initialize');
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('ContaoBlackForest\Contao\Core\DcGeneral\Service\CalendarFeedService', 'initialize');

$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('ContaoBlackForest\Contao\Core\DcGeneral\Service\FormService', 'initialize');

$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('ContaoBlackForest\Contao\Core\DcGeneral\Service\CommentsService', 'initialize');
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('ContaoBlackForest\Contao\Core\DcGeneral\Callback\CommentsCallback', 'initialize');

$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('ContaoBlackForest\Contao\Core\DcGeneral\Service\ThemeService', 'initialize');
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('ContaoBlackForest\Contao\Core\DcGeneral\Callback\ThemeCallback', 'initialize');
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('ContaoBlackForest\Contao\Core\DcGeneral\Service\ModuleService', 'initialize');
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('ContaoBlackForest\Contao\Core\DcGeneral\Service\LayoutService', 'initialize');
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('ContaoBlackForest\Contao\Core\DcGeneral\Service\StyleSheetService', 'initialize');
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('ContaoBlackForest\Contao\Core\DcGeneral\Service\ImageSizeService', 'initialize');

$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('ContaoBlackForest\Contao\Core\DcGeneral\Service\MemberService', 'initialize');
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('ContaoBlackForest\Contao\Core\DcGeneral\Service\MemberGroupService', 'initialize');
