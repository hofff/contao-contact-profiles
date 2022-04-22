<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\BackendTemplate;
use Contao\Module;
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
final class ContactProfileDetailModule extends Module
{
    use ContactProfileDetailTrait;

    /** @var string */
    protected $strTemplate = 'mod_hofff_contact_profile_detail';

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function renderBackendWildcard(): string
    {
        $template           = new BackendTemplate('be_wildcard');
        $template->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD'][$this->type][0]) . ' ###';
        $template->title    = $this->headline;
        $template->id       = $this->id;
        $template->link     = $this->name;
        $template->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

        return $template->parse();
    }
}
