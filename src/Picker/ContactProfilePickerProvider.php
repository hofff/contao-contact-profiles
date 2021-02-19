<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Picker;

use Contao\CoreBundle\Picker\AbstractInsertTagPickerProvider;
use Contao\CoreBundle\Picker\DcaPickerProviderInterface;
use Contao\CoreBundle\Picker\PickerConfig;
use Hofff\Contao\ContactProfiles\Model\ContactProfileRepository;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContactProfilePickerProvider extends AbstractInsertTagPickerProvider implements DcaPickerProviderInterface
{
    /**
     * @var Security
     */
    private $security;

    /**
     * @var ContactProfileRepository
     */
    private $repository;

    public function __construct(
        FactoryInterface $menuFactory,
        RouterInterface $router,
        ?TranslatorInterface $translator,
        Security $security,
        ContactProfileRepository $repository
    ) {
        parent::__construct($menuFactory, $router, $translator);

        $this->security   = $security;
        $this->repository = $repository;
    }

    public function getName(): string
    {
        return 'contactProfilePicker';
    }

    public function supportsContext($context): bool
    {
        return 'link' === $context && $this->security->isGranted('contao_user.modules', 'hofff_contact_profiles');
    }

    public function supportsValue(PickerConfig $config): bool
    {
        return $this->isMatchingInsertTag($config);
    }

    public function getDcaTable(): string
    {
        return 'tl_contact_profile';
    }

    public function getDcaAttributes(PickerConfig $config): array
    {
        $attributes = ['fieldType' => 'radio'];

        if ($source = $config->getExtra('source')) {
            $attributes['preserveRecord'] = $source;
        }

        if ($this->supportsValue($config)) {
            $attributes['value'] = $this->getInsertTagValue($config);
        }

        return $attributes;
    }

    public function convertDcaValue(PickerConfig $config, $value): string
    {
        return sprintf($this->getInsertTag($config), $value);
    }

    protected function getRouteParameters(PickerConfig $config = null): array
    {
        $params = ['do' => 'hofff_contact_profiles'];

        if (null === $config || !$config->getValue() || !$this->supportsValue($config)) {
            return $params;
        }

        if (null !== ($categoryId = $this->getCategoryId($this->getInsertTagValue($config)))) {
            $params['table'] = 'tl_contact_profile';
            $params['id']    = $categoryId;
        }

        return $params;
    }

    protected function getDefaultInsertTag(): string
    {
        return '{{contact_profile_url::%s}}';
    }

    /**
     * @param int|string $id
     */
    private function getCategoryId($id): ?int
    {
        $profile = $this->repository->fetchById((int) $id);
        if ($profile) {
            return (int) $profile['pid'];
        }

        return null;
    }
}
