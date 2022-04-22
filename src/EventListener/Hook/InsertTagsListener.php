<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Hook;

use Hofff\Contao\ContactProfiles\Model\ContactProfileRepository;
use Hofff\Contao\ContactProfiles\Routing\ContactProfileUrlGenerator;

use function explode;
use function in_array;

final class InsertTagsListener
{
    /** @var ContactProfileRepository */
    private $repository;

    /** @var ContactProfileUrlGenerator */
    private $urlGenerator;

    public function __construct(
        ContactProfileRepository $repository,
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

        if ($elements[0] !== 'contact_profile_url') {
            return false;
        }

        $profile = $this->repository->fetchById($elements[1]);
        if (! $profile) {
            return '';
        }

        $referenceType = in_array('absolute', $flags)
            ? ContactProfileUrlGenerator::ABSOLUTE_URL
            : ContactProfileUrlGenerator::ABSOLUTE_PATH;

        return $this->urlGenerator->generateDetailUrl($profile, $referenceType) ?: false;
    }
}
