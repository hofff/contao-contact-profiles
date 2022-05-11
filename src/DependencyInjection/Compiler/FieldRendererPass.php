<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\DependencyInjection\Compiler;

use Hofff\Contao\ContactProfiles\Renderer\FieldRenderer;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use function sprintf;

final class FieldRendererPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        if (! $container->hasDefinition(FieldRenderer::class)) {
            return;
        }

        $definition     = $container->getDefinition(FieldRenderer::class);
        $renderer       = (array) $definition->getArgument(1);
        $taggedServices = $container->findTaggedServiceIds('hofff_contao_contact_profiles.field_renderer');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                if (! isset($attributes['field'])) {
                    throw new InvalidConfigurationException(
                        sprintf('Service "%s" tagged as field renderer but no field defined', $id)
                    );
                }

                $renderer[$attributes['field']] = new Reference($id);
            }
        }

        $definition->setArgument(1, $renderer);
    }
}
