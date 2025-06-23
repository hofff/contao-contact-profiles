<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Picker;

use Contao\CoreBundle\Picker\AbstractInsertTagPickerProvider;
use Contao\CoreBundle\Picker\DcaPickerProviderInterface;
use Contao\CoreBundle\Picker\PickerConfig;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Knp\Menu\FactoryInterface;
use Override;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security as LegacySecurity;
use Symfony\Contracts\Translation\TranslatorInterface;

use function sprintf;

final class ContactProfilePickerProvider extends AbstractInsertTagPickerProvider implements DcaPickerProviderInterface
{
    public function __construct(
        FactoryInterface $menuFactory,
        RouterInterface $router,
        TranslatorInterface $translator,
        private readonly LegacySecurity|Security $security,
        private readonly ProfileRepository $repository,
    ) {
        parent::__construct($menuFactory, $router, $translator);
    }

    #[Override]
    public function getName(): string
    {
        return 'contactProfilePicker';
    }

    /** @param mixed $context */
    #[Override]
    public function supportsContext($context): bool
    {
        return $context === 'link' && $this->security->isGranted('contao_user.modules', 'hofff_contact_profiles');
    }

    #[Override]
    public function supportsValue(PickerConfig $config): bool
    {
        return $this->isMatchingInsertTag($config);
    }

    #[Override]
    public function getDcaTable(PickerConfig|null $config = null): string
    {
        return 'tl_contact_profile';
    }

    /** @return array<string,mixed> */
    #[Override]
    public function getDcaAttributes(PickerConfig $config): array
    {
        $attributes = ['fieldType' => 'radio'];
        $source     = $config->getExtra('source');

        if ($source) {
            $attributes['preserveRecord'] = $source;
        }

        if ($this->supportsValue($config)) {
            $attributes['value'] = $this->getInsertTagValue($config);
        }

        return $attributes;
    }

    /** @param mixed $value */
    #[Override]
    public function convertDcaValue(PickerConfig $config, $value): string
    {
        return sprintf($this->getInsertTag($config), $value);
    }

    /** {@inheritDoc} */
    #[Override]
    protected function getRouteParameters(PickerConfig|null $config = null): array
    {
        $params = ['do' => 'hofff_contact_profiles'];

        if ($config === null || ! $config->getValue() || ! $this->supportsValue($config)) {
            return $params;
        }

        $categoryId = $this->getCategoryId($this->getInsertTagValue($config));
        if ($categoryId !== null) {
            $params['table'] = 'tl_contact_profile';
            $params['id']    = $categoryId;
        }

        return $params;
    }

    #[Override]
    protected function getDefaultInsertTag(): string
    {
        return '{{contact_profile_url::%s}}';
    }

    /** @param int|string $categoryId */
    private function getCategoryId($categoryId): int|null
    {
        $profile = $this->repository->find((int) $categoryId);
        if ($profile instanceof Profile) {
            return (int) $profile->pid;
        }

        return null;
    }
}
