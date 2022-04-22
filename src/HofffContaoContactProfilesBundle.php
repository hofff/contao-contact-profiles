<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles;

use Hofff\Contao\ContactProfiles\DependencyInjection\Compiler\ImageFieldRendererPass;
use Hofff\Contao\ContactProfiles\DependencyInjection\Compiler\ProvideServicesPublicPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class HofffContaoContactProfilesBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ImageFieldRendererPass());
        $container->addCompilerPass(new ProvideServicesPublicPass());
    }
}
