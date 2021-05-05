<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;

use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;

use function array_filter;

final class ContactFieldsOptions
{
    /** @var ContaoFrameworkInterface */
    private $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @return string[]
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __invoke(): array
    {
        /** @var Adapter<Controller> $adpater */
        $adpater = $this->framework->getAdapter(Controller::class);
        $adpater->loadDataContainer('tl_contact_profile');
        $adpater->loadLanguageFile('tl_contact_profile');

        $fields = array_filter(
            $GLOBALS['TL_DCA']['tl_contact_profile']['fields'] ?? [],
            static function (array $config) {
                return $config['eval']['profileField'] ?? false;
            }
        );

        $options = [];

        foreach ($fields as $name => $config) {
            $options[$name] = $config['label'][0] ?? $name;
        }

        return $options;
    }
}
