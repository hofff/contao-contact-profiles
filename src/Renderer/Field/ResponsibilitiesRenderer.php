<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FrontendTemplate;
use Hofff\Contao\ContactProfiles\Query\ResponsibilitiesQuery;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;

final class ResponsibilitiesRenderer extends AbstractFieldRenderer
{
    protected const TEMPLATE = 'hofff_contact_field_responsibilities';

    /** @var ResponsibilitiesQuery */
    private $query;

    public function __construct(ContaoFrameworkInterface $framework, ResponsibilitiesQuery $query)
    {
        parent::__construct($framework);

        $this->query = $query;
    }

    protected function compile(FrontendTemplate $template, $value, ContactProfileRenderer $renderer): void
    {
        $template->value = ($this->query)((array) $value);
    }
}
