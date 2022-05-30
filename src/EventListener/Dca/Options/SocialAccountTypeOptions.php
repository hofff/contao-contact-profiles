<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca\Options;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Model\Collection;
use Hofff\Contao\ContactProfiles\Model\SocialAccount\SocialAccount;
use Hofff\Contao\ContactProfiles\Model\SocialAccount\SocialAccountRepository;

use function assert;

/** @Callback(table="tl_contact_profile", target="fields.accounts.eval.columnFields.type.options") */
final class SocialAccountTypeOptions
{
    private SocialAccountRepository $socialAccounts;

    public function __construct(SocialAccountRepository $socialAccounts)
    {
        $this->socialAccounts = $socialAccounts;
    }

    /** @return string[] */
    public function __invoke(): array
    {
        $options    = [];
        $collection = $this->socialAccounts->findAll(['order' => '.name']);
        if (! $collection instanceof Collection) {
            return $options;
        }

        foreach ($collection as $account) {
            assert($account instanceof SocialAccount);

            $options[$account->socialAccountId()] = $account->name;
        }

        return $options;
    }
}
