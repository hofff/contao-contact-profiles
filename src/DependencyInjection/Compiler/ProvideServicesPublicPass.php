<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ProvideServicesPublicPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (! $container->hasDefinition('netzmacht.contao_toolkit.repository_manager')) {
            return;
        }

        $definition = $container->getDefinition('netzmacht.contao_toolkit.repository_manager');
        $definition->setPublic(true);
    }
}
