<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\SocialTags;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\File;
use Contao\PageModel;
use Contao\StringUtil;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Model\SocialAccount\SocialAccount;
use Hofff\Contao\ContactProfiles\Model\SocialAccount\SocialAccountRepository;
use Hofff\Contao\ContactProfiles\Routing\ContactProfileUrlGenerator;
use Hofff\Contao\SocialTags\Data\Extractor\AbstractExtractor;
use Hofff\Contao\SocialTags\Data\OpenGraph\OpenGraphExtractor;
use Hofff\Contao\SocialTags\Data\OpenGraph\OpenGraphImageData;
use Hofff\Contao\SocialTags\Data\OpenGraph\OpenGraphType;
use Hofff\Contao\SocialTags\Data\TwitterCards\TwitterCardsExtractor;
use Hofff\Contao\SocialTags\Util\TypeUtil;
use Override;
use Symfony\Component\HttpFoundation\RequestStack;

use function str_replace;
use function strip_tags;
use function strpos;
use function trim;

/**
 * @implements OpenGraphExtractor<Profile, PageModel>
 * @implements TwitterCardsExtractor<Profile, PageModel>
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods))
 */
final class ContactProfileExtractor extends AbstractExtractor implements OpenGraphExtractor, TwitterCardsExtractor
{
    /** @SuppressWarnings(PHPMD.LongVariable) */
    public function __construct(
        ContaoFramework $framework,
        RequestStack $requestStack,
        ResponseContextAccessor $responseContextAccessor,
        InsertTagParser $insertTagParser,
        private ContactProfileUrlGenerator $urlGenerator,
        private SocialAccountRepository $socialAccounts,
        string $projectDir,
    ) {
        parent::__construct($framework, $requestStack, $responseContextAccessor, $insertTagParser, $projectDir);
    }

    /** {@inheritDoc} */
    #[Override]
    public function supportedDataContainers(): array
    {
        return [Profile::getTable()];
    }

    #[Override]
    public function supports(object $reference, object|null $fallback = null): bool
    {
        if (! $reference instanceof Profile) {
            return false;
        }

        return $fallback instanceof PageModel;
    }

    #[Override]
    public function extractOpenGraphImageData(object $reference, object|null $fallback = null): OpenGraphImageData
    {
        $imageData = new OpenGraphImageData();
        $file      = $this->getImage('image', $reference);
        $fileUrl   = $this->getFileUrl($file);

        if ($file && $fileUrl !== null) {
            $objImage = new File($file->path);
            $imageData->setURL($fileUrl);
            $imageData->setMIMEType($objImage->mime);
            $imageData->setWidth($objImage->width);
            $imageData->setHeight($objImage->height);
        }

        return $imageData;
    }

    #[Override]
    public function extractOpenGraphTitle(object $reference, object|null $fallback = null): string
    {
        $title = trim($reference->firstname . ' ' . $reference->lastname);
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        if (TypeUtil::isStringWithContent($title)) {
            return $this->replaceInsertTags($title);
        }

        return '';
    }

    #[Override]
    public function extractOpenGraphUrl(object $reference, object|null $fallback = null): string
    {
        return (string) $this->urlGenerator->generateDetailUrl(
            $reference,
            ContactProfileUrlGenerator::ABSOLUTE_URL,
        );
    }

    #[Override]
    public function extractOpenGraphDescription(object $reference, object|null $fallback = null): string|null
    {
        if (! TypeUtil::isStringWithContent($reference->teaser)) {
            return null;
        }

        /** @psalm-var string $description */
        $description = $reference->teaser;
        $description = trim(str_replace(["\n", "\r"], [' ', ''], $description));
        $description = $this->replaceInsertTags($description);
        $description = strip_tags($description);

        return $description ?: null;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    #[Override]
    public function extractOpenGraphSiteName(object $reference, object|null $fallback = null): string
    {
        return $fallback ? strip_tags($fallback->rootPageTitle ?: $fallback->rootTitle) : '';
    }

    #[Override]
    public function extractOpenGraphType(object $reference, object|null $fallback = null): OpenGraphType
    {
        return new OpenGraphType('profile');
    }

    #[Override]
    public function extractTwitterTitle(object $reference, object|null $fallback = null): string
    {
        $title = trim($reference->firstname . ' ' . $reference->lastname);
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        if (TypeUtil::isStringWithContent($title)) {
            return $this->replaceInsertTags($title);
        }

        return '';
    }

    #[Override]
    public function extractTwitterDescription(object $reference, object|null $fallback = null): string|null
    {
        if (! TypeUtil::isStringWithContent($reference->teaser)) {
            return null;
        }

        /** @psalm-var string $description */
        $description = $reference->teaser;
        $description = trim(str_replace(["\n", "\r"], [' ', ''], $description));
        $description = $this->replaceInsertTags($description);
        $description = strip_tags($description);

        return $description ?: null;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    #[Override]
    public function extractTwitterSite(object $reference, object|null $fallback = null): string|null
    {
        /** @psalm-suppress RiskyTruthyFalsyComparison */
        return $fallback?->hofff_st_twitter_site ?: null;
    }

    #[Override]
    public function extractTwitterImage(object $reference, object|null $fallback = null): string|null
    {
        return $this->getFileUrl($this->getImage('image', $reference));
    }

    #[Override]
    public function extractTwitterCreator(object $reference, object|null $fallback = null): string|null
    {
        $socialAccount = $this->socialAccounts->findOneBy(['.twitterCreator=?'], ['1']);
        if (! $socialAccount instanceof SocialAccount) {
            return null;
        }

        $accounts = StringUtil::deserialize($reference->accounts, true);

        foreach ($accounts as $account) {
            if ((int) $account['type'] !== (int) $socialAccount->id) {
                continue;
            }

            if (! $account['url']) {
                return null;
            }

            if (strpos($account['url'], '@') !== 0) {
                return '@' . $account['url'];
            }

            return $account['url'];
        }

        return null;
    }
}
