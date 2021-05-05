<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FrontendTemplate;
use Hofff\Contao\ContactProfiles\Query\SocialAccountQuery;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;

use function array_filter;
use function array_key_exists;
use function array_merge;

final class SocialAccountsRenderer extends AbstractFieldRenderer
{
    protected const TEMPLATE = 'hofff_contact_field_accounts';

    /** @var SocialAccountQuery */
    private $query;

    /** @var string[][] */
    private $accounts = [];

    public function __construct(ContaoFrameworkInterface $framework, SocialAccountQuery $query)
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
     * @param mixed $accounts
     */
    protected function compile(FrontendTemplate $template, $accounts, ContactProfileRenderer $renderer): void
    {
        $value = [];

        foreach ((array) $accounts as $config) {
            if ($config['type'] === '' || $config['url'] === '') {
                continue;
            }

            $account = $this->accountById((int) $config['type']);
            if (! $account) {
                continue;
            }

            $value[] = array_merge($config, $account);
        }

        $template->value = $value;
    }

    /** @return string[][] */
    private function accountById(int $type): ?array
    {
        if (! array_key_exists($type, $this->accounts)) {
            $this->accounts[$type] = ($this->query)($type);
        }

        return $this->accounts[$type];
    }
}
