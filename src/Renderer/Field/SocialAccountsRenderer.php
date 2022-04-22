<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendTemplate;
use Hofff\Contao\ContactProfiles\Query\SocialAccountQuery;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;

use function array_filter;
use function array_key_exists;
use function array_merge;

final class SocialAccountsRenderer extends AbstractFieldRenderer
{
    /** @var string|null */
    protected $template = 'hofff_contact_field_accounts';

    /** @var SocialAccountQuery */
    private $query;

    /** @var string[][] */
    private $accounts = [];

    public function __construct(ContaoFramework $framework, SocialAccountQuery $query)
    {
        parent::__construct($framework);

        $this->query = $query;
    }

    /** {@inheritDoc} */
    protected function hasValue($value): bool
    {
        if (! parent::hasValue($value)) {
            return false;
        }

        $profiles = array_filter(
            (array) $value,
            static function (array $config) {
                return $config['type'] !== '' && $config['url'] !== '';
            }
        );

        return $profiles !== [];
    }

    /**
     * @param mixed $value
     */
    protected function compile(FrontendTemplate $template, $value, ContactProfileRenderer $renderer): void
    {
        $compiled = [];

        foreach ((array) $value as $config) {
            if ($config['type'] === '' || $config['url'] === '') {
                continue;
            }

            $account = $this->accountById((int) $config['type']);
            if (! $account) {
                continue;
            }

            $compiled[] = array_merge($config, $account);
        }

        $template->value = $compiled;
    }

    /** @return string[] */
    private function accountById(int $type): array
    {
        if (! array_key_exists($type, $this->accounts)) {
            $this->accounts[$type] = ($this->query)($type);
        }

        return $this->accounts[$type];
    }
}
