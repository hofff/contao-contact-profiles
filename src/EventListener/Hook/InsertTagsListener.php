<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Hook;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Hofff\Contao\ContactProfiles\Routing\ContactProfileUrlGenerator;

use function explode;
use function in_array;

/**
 * @Hook("replaceInsertTags")
 */
final class InsertTagsListener
{
    private ProfileRepository $repository;

    private ContactProfileUrlGenerator $urlGenerator;

    public function __construct(
        ProfileRepository $repository,
        ContactProfileUrlGenerator $urlGenerator
    ) {
        $this->repository   = $repository;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param mixed        $cacheValue
     * @param list<string> $flags
     *
     * @return string|bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(string $tag, bool $useCache, $cacheValue, array $flags)
    {
        $elements = explode('::', $tag, 2);

        if ($elements[0] !== 'contact_profile_url' || ! isset($elements[1])) {
            return false;
        }

        $profile = $this->repository->find((int) $elements[1]);
        if (! $profile) {
            return '';
        }

        $referenceType = in_array('absolute', $flags)
            ? ContactProfileUrlGenerator::ABSOLUTE_URL
            : ContactProfileUrlGenerator::ABSOLUTE_PATH;

        /** @psalm-suppress RiskyTruthyFalsyComparison */
        return $this->urlGenerator->generateDetailUrl($profile, $referenceType) ?: false;
    }
}
