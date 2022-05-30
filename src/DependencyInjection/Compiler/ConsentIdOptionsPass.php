<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\DependencyInjection\Compiler;

use Hofff\Contao\Consent\Bridge\EventListener\Dca\ConsentIdOptions;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ConsentIdOptionsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (! $container->hasDefinition(ConsentIdOptions::class)) {
            return;
        }

        $definition = $container->getDefinition(ConsentIdOptions::class);
        $definition->addTag(
            'contao.callback',
            ['table' => 'tl_content', 'target' => 'fields.hofff_contact_consent_tag_youtube.options']
        );
        $definition->addTag(
            'contao.callback',
            ['table' => 'tl_content', 'target' => 'fields.hofff_contact_consent_tag_vimeo.options']
        );
        $definition->addTag(
            'contao.callback',
            ['table' => 'tl_module', 'target' => 'fields.hofff_contact_consent_tag_youtube.options']
        );
        $definition->addTag(
            'contao.callback',
            ['table' => 'tl_module', 'target' => 'fields.hofff_contact_consent_tag_vimeo.options']
        );
    }
}
