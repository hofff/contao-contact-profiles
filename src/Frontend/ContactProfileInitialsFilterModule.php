<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Patchwork\Utf8;

final class ContactProfileInitialsFilterModule extends ContentElement
{
    use ContactProfileInitialsFilterTrait;

    /** @var string */
    protected $strTemplate = 'mod_hofff_contact_profile_initials_filter';

    protected function renderBackendWildcard(): string
    {
        $objTemplate           = new BackendTemplate('be_wildcard');
        $objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD'][$this->type][0]) . ' ###';
        $objTemplate->title    = $this->headline;
        $objTemplate->id       = $this->id;
        $objTemplate->link     = $this->name;
        $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

        return $objTemplate->parse();
    }
}
