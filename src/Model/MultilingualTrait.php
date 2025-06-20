<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model;

use Contao\Model\Collection;
use Terminal42\DcMultilingualBundle\Model\MultilingualTrait as BaseMultilingualTrait;

trait MultilingualTrait
{
    use BaseMultilingualTrait;

    /**
     * @param array<string, mixed> $arrOptions
     *
     * @return Collection<static>|static[]|static|null A model, model collection or null if the result is empty
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingTraversableTypeHintSpecification
    protected static function find(array $arrOptions)
    {
        if (isset($arrOptions['language']) && $arrOptions['language'] === self::getFallbackLanguage()) {
            $arrOptions['language'] = '';
        }

        return parent::find($arrOptions);
    }
}
