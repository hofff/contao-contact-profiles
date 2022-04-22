<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Patchwork\Utf8;

/**
 * @property string|null $hofff_contact_more
 * @property string      $hofff_contact_template
 * @property string      $hofff_contact_fields
 * @property string      $hofff_contact_source
 * @property string      $hofff_contact_sources
 * @property string      $hofff_contact_profiles
 * @property string      $hofff_contact_categories
 * @property string      $hofff_contact_profiles_order
 * @property string      $hofff_contact_profiles_order_sql
 * @property string      $size
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ContactProfileInitialsFilterElement extends ContentElement
{
    use ContactProfileInitialsFilterTrait;

    /** @var string */
    protected $strTemplate = 'ce_hofff_contact_profile_initials_filter';

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function renderBackendWildcard(): string
    {
        $objTemplate           = new BackendTemplate('be_wildcard');
        $objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['CTE'][$this->type][0]) . ' ###';
        $objTemplate->title    = $this->headline;

        return $objTemplate->parse();
    }
}
