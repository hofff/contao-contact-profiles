<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Doctrine\DBAL\Types\Types;
use Netzmacht\Contao\Toolkit\Dca\DcaManager;
use Terminal42\DcMultilingualBundle\Driver;

final class MultilingualListener
{
    private DcaManager $dcaManager;

    private ?array $languages;

    private ?string $fallbackLanguage;

    private array $profileFields;

    public function __construct(
        DcaManager $dcaManager,
        ?array $languages,
        ?string $fallbackLanguage,
        array $profileFields
    ) {
        $this->dcaManager       = $dcaManager;
        $this->languages        = $languages;
        $this->fallbackLanguage = $fallbackLanguage;
        $this->profileFields    = $profileFields;
    }

    /** @Hook("loadDataContainer") */
    public function onLoadDataContainer(string $table): void
    {
        switch ($table) {
            case 'tl_contact_profile':
                $translatableFields = $this->profileFields;
                break;

            case 'tl_contact_responsibility':
            case 'tl_contact_social_account':
                $translatableFields = ['name'];
                break;

            default:
                return;
        }

        $definition = $this->dcaManager->getDefinition($table);
        $definition->modify(['config'], [$this, 'modifyConfigDefinition']);
        $definition->modify(['fields'], $this->modifyFieldsDefinition($translatableFields));
    }

    public function modifyConfigDefinition(array $config): array
    {
        $config['dataContainer']  = Driver::class;
        $config['langColumnName'] = 'multilingual_language';
        $config['langPid']        = 'multilingual_pid';

        if ($this->languages) {
            $config['languages'] = $this->languages;
        }

        if ($this->fallbackLanguage) {
            $config['fallbackLang'] = $this->fallbackLanguage;
        }

        $config['sql']['keys']['multilingual_language,multilingual_pid'] = 'index';

        return $config;
    }

    private function modifyFieldsDefinition(array $translatableFields): callable
    {
        return static function (array $fields) use ($translatableFields): array {
            $fields['multilingual_language']['sql'] = [
                'type'   => Types::STRING,
                'length'  => 5,
                'default' => '',
            ];
            $fields['multilingual_pid']['sql']      = [
                'type'    => Types::INTEGER,
                'unsigned' => true,
                'default'  => 0,
            ];

            foreach ($translatableFields as $field) {
                $fields[$field]['eval']['translatableFor'] = '*';
            }

            return $fields;
        };
    }
}
