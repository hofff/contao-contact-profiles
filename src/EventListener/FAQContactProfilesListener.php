<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FaqCategoryModel;
use Contao\FaqModel;
use Contao\Input;
use Contao\StringUtil;
use Hofff\Contao\ContactProfiles\Event\LoadContactProfilesEvent;
use Hofff\Contao\ContactProfiles\Model\ContactProfileRepository;
use Hofff\Contao\ContactProfiles\Util\ContactProfileUtil;

use function in_array;

final class FAQContactProfilesListener
{
    /** @var ContaoFrameworkInterface */
    private $framework;

    /** @var ContactProfileRepository */
    private $repository;

    public function __construct(ContaoFrameworkInterface $framework, ContactProfileRepository $repository)
    {
        $this->framework  = $framework;
        $this->repository = $repository;
    }

    public function onLoadContactProfiles(LoadContactProfilesEvent $event): void
    {
        if (! in_array('faq', $event->sources(), true)) {
            return;
        }

        $news = $this->getFAQ();
        if (! $news) {
            return;
        }

        $profileIds = StringUtil::deserialize($news->hofff_contact_profiles, true);
        $order      = StringUtil::deserialize($news->hofff_contact_profiles_order, true);
        $profiles   = $this->repository->fetchPublishedByProfileIds($profileIds);
        $profiles   = ContactProfileUtil::orderListByIds($profiles, $order);

        $event->setProfiles($profiles);
    }

    private function getFAQ(): ?FaqModel
    {
        $faqAlias = $this->getFAQAlias();
        if (! $faqAlias) {
            return null;
        }

        $faqCategory = $this->getFAQCategory();
        if (! $faqCategory) {
            return null;
        }

        $repository = $this->framework->getAdapter(FaqModel::class);

        return $repository->__call('findPublishedByParentAndIdOrAlias', [$faqAlias, [$faqCategory->id]]);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function getFAQAlias(): ?string
    {
        if (! isset($GLOBALS['objPage'])) {
            return null;
        }

        $inputAdapter  = $this->framework->getAdapter(Input::class);
        $configAdapter = $this->framework->getAdapter(Config::class);

        if ($configAdapter->__call('get', ['useAutoItem'])) {
            return $inputAdapter->__call('get', ['auto_item']);
        }

        return $inputAdapter->__call('get', ['items']);
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
