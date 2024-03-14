<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Doctrine\DBAL\Types\Types;
use Netzmacht\Contao\Toolkit\Dca\DcaManager;
use Terminal42\DcMultilingualBundle\Driver;

use function is_array;

final class MultilingualListener
{
    private DcaManager $dcaManager;

    /** @var list<string>|null */
    private ?array $languages;

    private ?string $fallbackLanguage;

    /** @var list<string> */
    private array $profileFields;

    /**
     * @param list<string>|null $languages
     * @param list<string>      $profileFields
     */
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

            case 'tl_contact_category':
                $translatableFields = ['title', 'jumpTo'];
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

    /**
     * @param array<string,mixed> $config
     *
     * @return array<string,mixed>
     */
    public function modifyConfigDefinition(array $config): array
    {
        /** @psalm-suppress MissingDependency */
        $config['dataContainer']  = Driver::class;
        $config['langColumnName'] = 'multilingual_language';
        $config['langPid']        = 'multilingual_pid';

        if ($this->languages !== []) {
            $config['languages'] = $this->languages;
        }

        if ($this->fallbackLanguage !== null) {
            $config['fallbackLang'] = $this->fallbackLanguage;
        }

        $config['sql']['keys']['multilingual_language,multilingual_pid'] = 'index';

        return $config;
    }

    /** @param list<string> $translatableFields */
    private function modifyFieldsDefinition(array $translatableFields): callable
    {
        /**
         * @param array<string,array<string,mixed>>
         *
         * @return array<string,array<string,mixed>>
         */
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

                if (! isset($fields[$field]['sql']) || ! is_array($fields[$field]['sql'])) {
                    continue;
                }

                $fields[$field]['sql']['notnull'] = false;
                $fields[$field]['sql']['default'] = null;
            }

            return $fields;
        };
    }
}
