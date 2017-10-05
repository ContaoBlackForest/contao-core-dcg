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


use ContaoBlackForest\Contao\Core\DcGeneral\Service\CalendarFeedService;
use ContaoBlackForest\Contao\Core\DcGeneral\Service\CalendarService;
use ContaoBlackForest\Contao\Core\DcGeneral\Service\CommentsService;
use ContaoBlackForest\Contao\Core\DcGeneral\Service\FaqService;
use ContaoBlackForest\Contao\Core\DcGeneral\Service\FormService;
use ContaoBlackForest\Contao\Core\DcGeneral\Service\ImageSizeService;
use ContaoBlackForest\Contao\Core\DcGeneral\Service\LayoutService;
use ContaoBlackForest\Contao\Core\DcGeneral\Service\ModuleService;
use ContaoBlackForest\Contao\Core\DcGeneral\Service\NewsFeedService;
use ContaoBlackForest\Contao\Core\DcGeneral\Service\NewsArchiveService;
use ContaoBlackForest\Contao\Core\DcGeneral\Service\NewsletterRecipientsService;
use ContaoBlackForest\Contao\Core\DcGeneral\Service\NewsletterService;
use ContaoBlackForest\Contao\Core\DcGeneral\Service\StyleSheetService;
use ContaoBlackForest\Contao\Core\DcGeneral\Service\TableToGeneralService;
use ContaoBlackForest\Contao\Core\DcGeneral\Service\ThemeService;

$container['dc-general.table_to_general'] = function($container) {
    return new TableToGeneralService();
};

$container['dc-general.table_to_general.news_tl_news_archive'] = $container->share(
    function ($container) {
        return new NewsArchiveService();
    }
);
$container['dc-general.table_to_general.news_tl_news'] = $container->share(
    function ($container) {
        return new NewsArchiveService();
    }
);
$container['dc-general.table_to_general.news_tl_content'] = $container->share(
    function ($container) {
        return new NewsArchiveService();
    }
);
$container['dc-general.table_to_general.news_tl_news_feed'] = $container->share(
    function ($container) {
        return new NewsFeedService();
    }
);

$container['dc-general.table_to_general.faq_tl_faq_category'] = $container->share(
    function ($container) {
        return new FaqService();
    }
);
$container['dc-general.table_to_general.faq_tl_faq'] = $container->share(
    function ($container) {
        return new FaqService();
    }
);

$container['dc-general.table_to_general.newsletter_tl_newsletter_channel'] = $container->share(
    function ($container) {
        return new NewsletterService();
    }
);
$container['dc-general.table_to_general.newsletter_tl_newsletter'] = $container->share(
    function ($container) {
        return new NewsletterService();
    }
);
$container['dc-general.table_to_general.newsletter_tl_newsletter_recipients'] = $container->share(
    function ($container) {
        return new NewsletterRecipientsService();
    }
);

$container['dc-general.table_to_general.calendar_tl_calendar'] = $container->share(
    function ($container) {
        return new CalendarService();
    }
);
$container['dc-general.table_to_general.calendar_tl_calendar_events'] = $container->share(
    function ($container) {
        return new CalendarService();
    }
);
$container['dc-general.table_to_general.calendar_tl_content'] = $container->share(
    function ($container) {
        return new CalendarService();
    }
);
$container['dc-general.table_to_general.calendar_tl_calendar_feed'] = $container->share(
    function ($container) {
        return new CalendarFeedService();
    }
);

$container['dc-general.table_to_general.form_tl_form'] = $container->share(
    function ($container) {
        return new FormService();
    }
);
$container['dc-general.table_to_general.form_tl_form_field'] = $container->share(
    function ($container) {
        return new FormService();
    }
);

$container['dc-general.table_to_general.comments_tl_comments'] = $container->share(
    function ($container) {
        return new CommentsService();
    }
);

$container['dc-general.table_to_general.themes_tl_theme'] = $container->share(
    function ($container) {
        return new ThemeService();
    }
);
$container['dc-general.table_to_general.themes_tl_module'] = $container->share(
    function ($container) {
        return new ModuleService();
    }
);
$container['dc-general.table_to_general.themes_tl_layout'] = $container->share(
    function ($container) {
        return new LayoutService();
    }
);
$container['dc-general.table_to_general.themes_tl_style_sheet'] = $container->share(
    function ($container) {
        return new StyleSheetService();
    }
);
$container['dc-general.table_to_general.themes_tl_style'] = $container->share(
    function ($container) {
        return new StyleSheetService();
    }
);
$container['dc-general.table_to_general.themes_tl_image_size'] = $container->share(
    function ($container) {
        return new ImageSizeService();
    }
);
$container['dc-general.table_to_general.themes_tl_image_size_item'] = $container->share(
    function ($container) {
        return new ImageSizeService();
    }
);
