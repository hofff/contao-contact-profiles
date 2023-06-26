<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\SocialTags;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\File;
use Contao\FilesModel;
use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Model\SocialAccount\SocialAccount;
use Hofff\Contao\ContactProfiles\Model\SocialAccount\SocialAccountRepository;
use Hofff\Contao\ContactProfiles\Routing\ContactProfileUrlGenerator;
use Hofff\Contao\SocialTags\Data\Extractor\AbstractExtractor;
use Hofff\Contao\SocialTags\Data\OpenGraph\OpenGraphImageData;
use Hofff\Contao\SocialTags\Data\OpenGraph\OpenGraphType;
use Hofff\Contao\SocialTags\Util\TypeUtil;
use Symfony\Component\HttpFoundation\RequestStack;

use function is_file;
use function method_exists;
use function str_replace;
use function strip_tags;
use function strpos;
use function trim;
use function ucfirst;

/** @SuppressWarnings(PHPMD.UnusedPrivateMethod) */
final class ContactProfileExtractor extends AbstractExtractor
{
    private ContactProfileUrlGenerator $urlGenerator;

    private SocialAccountRepository $socialAccounts;

    public function __construct(
        ContaoFramework $framework,
        RequestStack $requestStack,
        ContactProfileUrlGenerator $urlGenerator,
        SocialAccountRepository $socialAccounts,
        string $projectDir
    ) {
        parent::__construct($framework, $requestStack, $projectDir);

        $this->urlGenerator   = $urlGenerator;
        $this->socialAccounts = $socialAccounts;
    }

    public function supports(Model $reference, ?Model $fallback = null): bool
    {
        if (! $reference instanceof Profile) {
            return false;
        }

        return $fallback instanceof PageModel;
    }

    /** @return mixed */
    public function extract(string $type, string $field, Model $reference, ?Model $fallback = null)
    {
        $methodName = 'extract' . ucfirst($type) . ucfirst($field);

        if ($methodName !== __FUNCTION__ && method_exists($this, $methodName)) {
            return $this->$methodName($reference, $fallback);
        }

        return null;
    }

    /**
     * @param string|resource $strImage
     */
    private function extractOpenGraphImageData(Profile $contactProfile): OpenGraphImageData
    {
        $imageData = new OpenGraphImageData();
        if ($contactProfile->image === null) {
            return $imageData;
        }

        $fileModel = FilesModel::findByUuid($contactProfile->image);

        if ($fileModel instanceof FilesModel && is_file($this->projectDir . '/' . $fileModel->path)) {
            $objImage = new File($fileModel->path);
            $imageData->setURL($this->getBaseUrl() . $fileModel->path);
            $imageData->setMIMEType($objImage->mime);
            $imageData->setWidth($objImage->width);
            $imageData->setHeight($objImage->height);
        }

        return $imageData;
    }

    private function extractOpenGraphTitle(Profile $contactProfile): ?string
    {
        $title = $contactProfile->firstname . ' ' . $contactProfile->lastname;
        if (TypeUtil::isStringWithContent($title)) {
            return $this->replaceInsertTags($title);
        }

        return null;
    }

    private function extractOpenGraphUrl(Profile $contactProfile): string
    {
        return (string) $this->urlGenerator->generateDetailUrl(
            $contactProfile,
            ContactProfileUrlGenerator::ABSOLUTE_URL
        );
    }

    private function extractOpenGraphDescription(Profile $contactProfile): ?string
    {
        if (! TypeUtil::isStringWithContent($contactProfile->teaser)) {
            return null;
        }

        /** @psalm-var string $description */
        $description = $contactProfile->teaser;
        $description = trim(str_replace(["\n", "\r"], [' ', ''], $description));
        $description = $this->replaceInsertTags($description);
        $description = strip_tags($description);

        return $description ?: null;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    private function extractOpenGraphSiteName(Profile $contactProfile, PageModel $fallback): string
    {
        return strip_tags($fallback->rootPageTitle ?: $fallback->rootTitle);
    }

    private function extractOpenGraphType(): OpenGraphType
    {
        return new OpenGraphType('profile');
    }

    private function extractTwitterTitle(Profile $contactProfile): ?string
    {
        $title = $contactProfile->firstname . ' ' . $contactProfile->lastname;
        if (TypeUtil::isStringWithContent($title)) {
            return $this->replaceInsertTags($title);
        }

        return null;
    }

    private function extractTwitterDescription(Profile $contactProfile): ?string
    {
        if (! TypeUtil::isStringWithContent($contactProfile->teaser)) {
            return null;
        }

        /** @psalm-var string $description */
        $description = $contactProfile->teaser;
        $description = trim(str_replace(["\n", "\r"], [' ', ''], $description));
        $description = $this->replaceInsertTags($description);
        $description = strip_tags($description);

        return $description ?: null;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    private function extractTwitterSite(Profile $contactProfile, PageModel $referencePage): ?string
    {
        return $referencePage->hofff_st_twitter_site ?: null;
    }

    private function extractTwitterImage(Profile $contactProfile): ?string
    {
        if (! $contactProfile->image) {
            return null;
        }

        $file = FilesModel::findByUuid($contactProfile->image);

        if ($file && is_file($this->projectDir . '/' . $file->path)) {
            return $this->getBaseUrl() . $file->path;
        }

        return null;
    }

    private function extractTwitterCreator(Profile $contactProfile): ?string
    {
        $socialAccount = $this->socialAccounts->findOneBy(['.twitterCreator=?'], ['1']);
        if (! $socialAccount instanceof SocialAccount) {
            return null;
        }

        $accounts = StringUtil::deserialize($contactProfile->accounts, true);

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
