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


$container->provideSymfonyService('dc-general.table_to_general');
$container->provideSymfonyService('dc-general.table_to_general.news_tl_news');
$container->provideSymfonyService('dc-general.table_to_general.news_tl_news_archive');
$container->provideSymfonyService('dc-general.table_to_general.news_tl_content');
$container->provideSymfonyService('dc-general.table_to_general.news_tl_news_feed');


$container->provideSymfonyService('dc-general.table_to_general.newsletter_tl_newsletter_channel');
$container->provideSymfonyService('dc-general.table_to_general.newsletter_tl_newsletter');
$container->provideSymfonyService('dc-general.table_to_general.newsletter_tl_newsletter_recipients');

$container->provideSymfonyService('dc-general.table_to_general.calendar_tl_calendar');
$container->provideSymfonyService('dc-general.table_to_general.calendar_tl_calendar_events');
$container->provideSymfonyService('dc-general.table_to_general.calendar_tl_content');
$container->provideSymfonyService('dc-general.table_to_general.calendar_tl_calendar_feed');

$container->provideSymfonyService('dc-general.table_to_general.form_tl_form');
$container->provideSymfonyService('dc-general.table_to_general.form_tl_form_field');

$container->provideSymfonyService('dc-general.table_to_general.comments_tl_comments');

$container->provideSymfonyService('dc-general.table_to_general.themes_tl_theme');
$container->provideSymfonyService('dc-general.table_to_general.themes_tl_module');
$container->provideSymfonyService('dc-general.table_to_general.themes_tl_style_sheet');
$container->provideSymfonyService('dc-general.table_to_general.themes_tl_style');
$container->provideSymfonyService('dc-general.table_to_general.themes_tl_layout');
$container->provideSymfonyService('dc-general.table_to_general.themes_tl_image_size');
$container->provideSymfonyService('dc-general.table_to_general.themes_tl_image_size_item');
