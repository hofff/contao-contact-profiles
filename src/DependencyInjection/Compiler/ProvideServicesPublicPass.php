<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\DependencyInjection\Compiler;

use Hofff\Contao\Consent\Bridge\ConsentId\ConsentIdParser;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ProvideServicesPublicPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (! $container->hasDefinition(ConsentIdParser::class)) {
            return;
        }

        $definition = $container->getDefinition(ConsentIdParser::class);
        $definition->setPublic(true);
    }
}
