<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener;

use Contao\FaqCategoryModel;
use Contao\FaqModel;
use Contao\Model;

final class FAQContactProfilesListener extends SourceListener
{
    protected function source(): string
    {
        return 'faq';
    }

    protected function fetchSource(string $alias): ?Model
    {
        $faqCategory = $this->getFAQCategory();
        if (! $faqCategory) {
            return null;
        }

        $repository = $this->framework->getAdapter(FaqModel::class);

        return $repository->__call('findPublishedByParentAndIdOrAlias', [$alias, [$faqCategory->id]]);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function getFAQCategory(): ?FaqCategoryModel
    {
        $repository = $this->framework->getAdapter(FaqCategoryModel::class);

        return $repository->__call('findOneByJumpTo', [$GLOBALS['objPage']->id]);
    }
}
