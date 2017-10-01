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

$container->provideSymfonyService('dc-general.table_to_general.faq_tl_faq_category');
$container->provideSymfonyService('dc-general.table_to_general.faq_tl_faq');

$container->provideSymfonyService('dc-general.table_to_general.newsletter_tl_newsletter_channel');
$container->provideSymfonyService('dc-general.table_to_general.newsletter_tl_newsletter');
$container->provideSymfonyService('dc-general.table_to_general.newsletter_tl_newsletter_recipients');
