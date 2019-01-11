<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\NewsBundle\ContaoNewsBundle;
use Hofff\Contao\ContactProfiles\HofffContaoContactProfilesBundle;

final class Plugin implements BundlePluginInterface
{
    /** @return BundleConfig[] */
    public function getBundles(ParserInterface $parser) : array
    {
        return [BundleConfig::create(HofffContaoContactProfilesBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class, ContaoNewsBundle::class]),
        ];
    }
}
