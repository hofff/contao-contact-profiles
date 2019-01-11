<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;

use Contao\Backend;
use Contao\BackendUser;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use function func_get_arg;
use function is_array;
use function is_callable;
use function sprintf;
use function time;

final class ContactProfileDcaListener
{
    /** @param string[] $row */
    public function generateRow(array $row) : string
    {
        $label = $row['lastname'];

        if ($row['firstname']) {
            $label .= ', ' . $row['firstname'];
        }

        return $label;
    }

    /**
     * Return the "toggle visibility" button
     *
     * @param string[] $row
     */
    public function toggleIcon(array $row, ?string $href, string $label, string $title, string $icon, string $attributes) : string
    {
        if (Input::get('tid') !== null && Input::get('tid') !== '') {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') === '1'), (@func_get_arg(12) ?: null));
            Backend::redirect(Backend::getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (! BackendUser::getInstance()->hasAccess('tl_contact_profile::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (! $row['published']) {
            $icon = 'invisible.svg';
        }

        return sprintf(
            '<a href="%s" title="%s"%s>%s</a> ',
            Backend::addToUrl($href),
            StringUtil::specialchars($title),
            $attributes,
            Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"')
        );
    }

    /**
     * Disable/enable a user group
     *
     * @throws AccessDeniedException
     */
    public function toggleVisibility(int $intId, bool $blnVisible, ?DataContainer $dc = null) : void
    {
        // Set the ID and action
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        if ($dc) {
            $dc->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (is_array($GLOBALS['TL_DCA']['tl_contact_profile']['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_contact_profile']['config']['onload_callback'] as $callback) {
                if (is_array($callback)) {
                    $callback[0] = System::importStatic($callback[0]);
                    $callback[0]->{$callback[1]}($dc);
                } elseif (is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        // Check the field access
        if (! BackendUser::getInstance()->hasAccess('tl_contact_profile::published', 'alexf')) {
            throw new AccessDeniedException('Not enough permissions to publish/unpublish article ID "' . $intId . '".');
        }

        // Set the current record
        if ($dc) {
            $objRow = Database::getInstance()->prepare('SELECT * FROM tl_contact_profile WHERE id=?')
                ->limit(1)
                ->execute($intId);

            if ($objRow->numRows) {
                $dc->activeRecord = $objRow;
            }
        }

        $objVersions = new Versions('tl_contact_profile', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_contact_profile']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_contact_profile']['fields']['published']['save_callback'] as $callback) {
                if (is_array($callback)) {
                    $callback[0] = System::importStatic($callback[0]);
                    $blnVisible  = $callback[0]->{$callback[1]}($dc);
                } elseif (is_callable($callback)) {
                    $blnVisible = $callback($dc);
                }
            }
        }

        $time = time();

        // Update the database
        Database::getInstance()
            ->prepare(
                sprintf('UPDATE tl_contact_profile SET tstamp=%s, published=\'\'', $time)
                . ($blnVisible ? '1' : '')
                . "' WHERE id=?"
            )
            ->execute($intId);

        if ($dc && $dc->activeRecord) {
            $dc->activeRecord->tstamp    = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (is_array($GLOBALS['TL_DCA']['tl_contact_profile']['config']['onsubmit_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_contact_profile']['config']['onsubmit_callback'] as $callback) {
                if (is_array($callback)) {
                    $callback[0] = System::importStatic($callback[0]);
                    $callback[0]->{$callback[1]}($dc);
                } elseif (is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        $objVersions->create();
    }
}
