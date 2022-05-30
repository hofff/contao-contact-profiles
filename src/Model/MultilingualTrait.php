<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model;

use Terminal42\DcMultilingualBundle\Model\MultilingualTrait as BaseMultilingualTrait;

trait MultilingualTrait
{
    use BaseMultilingualTrait;

    /** {@inheritDoc} */
    protected static function find(array $arrOptions)
    {
        if (isset($arrOptions['language']) && $arrOptions['language'] === self::getFallbackLanguage()) {
            $arrOptions['language'] = '';
        }

        return parent::find($arrOptions);
    }
}
