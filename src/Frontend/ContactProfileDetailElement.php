<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Patchwork\Utf8;

class ContactProfileDetailElement extends ContentElement
{
    use ContactProfileDetailTrait;

    /** @var string */
    protected $strTemplate = 'ce_hofff_contact_profile_detail';

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
