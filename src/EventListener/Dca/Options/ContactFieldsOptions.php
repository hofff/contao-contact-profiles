<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca\Options;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Netzmacht\Contao\Toolkit\Dca\DcaManager;

use function array_filter;

/**
 * @Callback(table="tl_content", target="fields.hofff_contact_fields.options")
 * @Callback(table="tl_module", target="fields.hofff_contact_fields.options")
 */
final class ContactFieldsOptions
{
    private DcaManager $dcaManager;

    public function __construct(DcaManager $dcaManager)
    {
        $this->dcaManager = $dcaManager;
    }

    /**
     * @return string[]
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __invoke(): array
    {
        $fields = array_filter(
            (array) $this->dcaManager->getDefinition(Profile::getTable())->get(['fields']),
            static function (array $config): bool {
                return (bool) ($config['eval']['profileField'] ?? false);
            }
        );

        $options = [];

        foreach ($fields as $name => $config) {
            $options[$name] = $config['label'][0] ?? $name;
        }

        return $options;
    }
}
