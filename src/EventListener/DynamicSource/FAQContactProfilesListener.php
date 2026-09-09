<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\DynamicSource;

use Contao\FaqCategoryModel;
use Contao\FaqModel;
use Contao\Model;
use Override;

final class FAQContactProfilesListener extends DynamicSourceListener
{
    #[Override]
    protected function source(): string
    {
        return 'faq';
    }

    #[Override]
    protected function fetchSource(string $alias): Model|null
    {
        $faqCategory = $this->getFAQCategory();
        if (! $faqCategory) {
            return null;
        }

        $repository = $this->framework->getAdapter(FaqModel::class);

        return $repository->__call('findPublishedByParentAndIdOrAlias', [$alias, [$faqCategory->id]]);
    }

    /** @SuppressWarnings(PHPMD.Superglobals) */
    private function getFAQCategory(): FaqCategoryModel|null
    {
        $repository = $this->framework->getAdapter(FaqCategoryModel::class);

        return $repository->__call('findOneByJumpTo', [$GLOBALS['objPage']->id]);
    }
}
