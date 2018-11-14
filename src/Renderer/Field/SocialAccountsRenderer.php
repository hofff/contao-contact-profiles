<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FrontendTemplate;
use Hofff\Contao\ContactProfiles\Query\SocialAccountQuery;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;
use function array_key_exists;

final class SocialAccountsRenderer extends AbstractFieldRenderer
{
    protected const TEMPLATE = 'hofff_contact_field_accounts';

    /** @var SocialAccountQuery */
    private $query;

    /** @var array */
    private $accounts = [];

    public function __construct(ContaoFrameworkInterface $framework, SocialAccountQuery $query)
    {
        parent::__construct($framework);

        $this->query = $query;
    }

    protected function compile(FrontendTemplate $template, $accounts, ContactProfileRenderer $renderer): void
    {
        $value = [];

        foreach ((array) $accounts as $config) {
            if ($config['type'] === '' || $config['url'] === '') {
                continue;
            }

            $account = $this->accountById((int) $config['type']);
            if (!$account) {
                continue;
            }

            $value[] = array_merge($config, $account);
        }

        $template->value = $value;
    }

    private function accountById(int $type): ?array
    {
        if (!array_key_exists($type, $this->accounts)) {
            $this->accounts[$type] = ($this->query)($type);
        }

        return $this->accounts[$type];
    }
}
