<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\SocialTags;

use Contao\File;
use Contao\FilesModel;
use Contao\Model;
use Contao\PageModel;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\SocialTags\Data\Extractor\AbstractExtractor;
use Hofff\Contao\SocialTags\Data\OpenGraph\OpenGraphImageData;
use Hofff\Contao\SocialTags\Data\OpenGraph\OpenGraphType;
use Hofff\Contao\SocialTags\Util\TypeUtil;

use function is_file;
use function method_exists;
use function str_replace;
use function strip_tags;
use function trim;
use function ucfirst;

/** @SuppressWarnings(PHPMD.UnusedPrivateMethod) */
final class ContactProfileExtractor extends AbstractExtractor
{
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

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function extractOpenGraphUrl(Profile $contactProfile): string
    {
        return $GLOBALS['objPage']->getAbsoluteurl('/' . $contactProfile->alias);
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
}
