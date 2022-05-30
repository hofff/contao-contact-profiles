<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendTemplate;
use Contao\StringUtil;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Model\SocialAccount\SocialAccount;
use Hofff\Contao\ContactProfiles\Model\SocialAccount\SocialAccountRepository;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;

use function array_filter;
use function array_key_exists;
use function array_merge;

final class SocialAccountsRenderer extends AbstractFieldRenderer
{
    protected ?string $template = 'hofff_contact_field_accounts';

    /** @var array<string|int,SocialAccount|null> */
    private array $accounts = [];

    private SocialAccountRepository $socialAccounts;

    public function __construct(ContaoFramework $framework, SocialAccountRepository $socialAccounts)
    {
        parent::__construct($framework);

        $this->socialAccounts = $socialAccounts;
    }

    /** {@inheritDoc} */
    public function hasValue(string $field, Profile $profile): bool
    {
        if (! parent::hasValue($field, $profile)) {
            return false;
        }

        $value    = StringUtil::deserialize($profile->$field);
        $profiles = array_filter(
            (array) $value,
            static function (array $config) {
                return $config['type'] !== '' && $config['url'] !== '';
            }
        );

        return $profiles !== [];
    }

    /** {@inheritDoc} */
    protected function compile(
        FrontendTemplate $template,
        $value,
        Profile $profile,
        ContactProfileRenderer $renderer
    ): void {
        $compiled = [];

        foreach ((array) $value as $config) {
            if ($config['type'] === '' || $config['url'] === '') {
                continue;
            }

            $account = $this->accountById((int) $config['type']);
            if (! $account) {
                continue;
            }

            $compiled[] = array_merge($config, $account->row());
        }

        $template->value = $compiled;
    }

    private function accountById(int $type): ?SocialAccount
    {
        if (! array_key_exists($type, $this->accounts)) {
            $this->accounts[$type] = $this->socialAccounts->find($type);
        }

        return $this->accounts[$type];
    }
}
