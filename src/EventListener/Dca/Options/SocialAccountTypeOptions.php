<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca\Options;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Model\Collection;
use Hofff\Contao\ContactProfiles\Model\SocialAccount\SocialAccountRepository;

#[AsCallback('tl_contact_profile', 'fields.accounts.eval.columnFields.type.options')]
final class SocialAccountTypeOptions
{
    public function __construct(private SocialAccountRepository $socialAccounts)
    {
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
            $options[$account->socialAccountId()] = $account->name;
        }

        return $options;
    }
}
