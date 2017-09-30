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


use ContaoBlackForest\Contao\Core\DcGeneral\Service\NewsFeedService;
use ContaoBlackForest\Contao\Core\DcGeneral\Service\NewsArchiveService;
use ContaoBlackForest\Contao\Core\DcGeneral\Service\TableToGeneralService;

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
