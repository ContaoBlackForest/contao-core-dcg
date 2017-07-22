<?php
/**
 * FRAMEWORK
 *
 * Copyright (C) FRAMEWORK
 *
 * @package   contao-core-dcg
 * @file      Plugin.php
 * @author    Sven Baumann <baumann.sv@gmail.com>
 * @author    Dominik Tomasi <dominik.tomasi@gmail.com>
 * @license   GNU/LGPL
 * @copyright Copyright 2017 owner
 */


namespace ContaoBlackForest\Contao\Core\DcGeneral\ContaoManager;


use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerBundle\ContaoManagerBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Config\ConfigInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use ContaoBlackForest\Contao\Core\DcGeneral\CbContaoCoreDcgBundle;
use ContaoCommunityAlliance\DcGeneral\CcaDcGeneralBundle;
use DependencyInjection\Container\CcaDependencyInjectionBundle;

class Plugin implements BundlePluginInterface
{

    /**
     * Gets a list of autoload configurations for this bundle.
     *
     * @param ParserInterface $parser
     *
     * @return ConfigInterface[]
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(CbContaoCoreDcgBundle::class)
                ->setLoadAfter(
                    [
                        ContaoCoreBundle::class,
                        ContaoManagerBundle::class,
                        CcaDependencyInjectionBundle::class,
                        CcaDcGeneralBundle::class
                    ]
                )
        ];
    }
}
