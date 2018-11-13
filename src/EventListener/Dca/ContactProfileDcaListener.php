<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;


final class ContactProfileDcaListener
{
    public function generateRow(array $row): string
    {
        $label = $row['lastname'];

        if ($row['firstname']) {
            $label .= ', ' . $row['firstname'];
        }

        return $label;
    }
}
