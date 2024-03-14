<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;

use Ausi\SlugGenerator\SlugGeneratorInterface;
use Contao\Backend;
use Contao\BackendUser;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use Doctrine\DBAL\Connection;
use Exception;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Netzmacht\Contao\Toolkit\Dca\DcaManager;
use RuntimeException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function func_get_arg;
use function is_array;
use function is_callable;
use function preg_match;
use function preg_replace_callback;
use function sprintf;
use function time;

/** @SuppressWarnings(PHPMD.ExcessiveClassComplexity) */
final class ContactProfileDcaListener
{
    private SlugGeneratorInterface $slugGenerator;

    private Connection $connection;

    private TranslatorInterface $translator;

    private DcaManager $dcaManager;

    private ProfileRepository $profiles;

    private SessionInterface $session;

    private string $pattern;

    private bool $multilingual;

    private ?string $fallbackLanguage;

    public function __construct(
        SlugGeneratorInterface $slugGenerator,
        Connection $connection,
        ProfileRepository $profiles,
        TranslatorInterface $translator,
        DcaManager $dcaManager,
        SessionInterface $session,
        string $aliasPattern,
        bool $multilingual,
        ?string $fallbackLanguage
    ) {
        $this->slugGenerator    = $slugGenerator;
        $this->connection       = $connection;
        $this->pattern          = $aliasPattern;
        $this->translator       = $translator;
        $this->dcaManager       = $dcaManager;
        $this->profiles         = $profiles;
        $this->multilingual     = $multilingual;
        $this->session          = $session;
        $this->fallbackLanguage = $fallbackLanguage;
    }

    /** @Callback(table="tl_contact_profile", target="config.onload") */
    public function onLoad(): void
    {
        /** @psalm-suppress UndefinedInterfaceMethod */
        $sorting = $this->session->getBag('contao_backend')->get('sorting')['tl_contact_profile'] ?? null;

        // Only set sorting as the first field if custom sorting is chosen.
        if ($sorting !== 'sorting') {
            return;
        }

        $this->dcaManager->getDefinition('tl_contact_profile')->set(['list', 'sorting', 'fields'], ['sorting']);
    }

    /**
     * @param mixed $value
     *
     * @Callback(table="tl_contact_profile", target="fields.alias.save")
     */
    public function generateAlias($value, DataContainer $dataContainer): string
    {
        // Generate alias if there is none
        if (! $value) {
            $value = $this->determineAlias($dataContainer);
        }

        if (preg_match('/^[1-9]\d*$/', $value)) {
            throw new Exception($this->translator->trans('ERR.aliasNumeric', [$value], 'contao_default'));
        }

        if ($this->aliasExists($value, $dataContainer)) {
            throw new Exception($this->translator->trans('ERR.aliasExists', [$value], 'contao_default'));
        }

        return $value;
    }

    private function determineAlias(DataContainer $dataContainer): string
    {
        if (! $dataContainer->activeRecord) {
            throw new RuntimeException('Unable to generate alias');
        }

        $profile = $this->profiles->findOneBy(
            ['.id=?'],
            [$dataContainer->id],
            ['language' => $dataContainer->activeRecord->multilingual_language]
        );

        if (! $profile instanceof Profile) {
            throw new RuntimeException('Unable to generate alias');
        }

        $alias = preg_replace_callback(
            '/{([^}]+)}/',
            /** @return mixed */
            static function (array $matches) use ($profile) {
                return StringUtil::prepareSlug($profile->{$matches[1]});
            },
            $this->pattern
        );

        $options = [];
        if ($this->multilingual) {
            if ($dataContainer->activeRecord->multilingual_language) {
                $options['locale'] = $dataContainer->activeRecord->multilingual_language;
            } elseif ($this->fallbackLanguage !== null) {
                $options['locale'] = $this->fallbackLanguage;
            }
        }

        $alias  = $this->slugGenerator->generate($alias, $options);
        $value  = $this->slugGenerator->generate($alias, $options);
        $suffix = 2;

        while ($this->aliasExists($value, $dataContainer)) {
            $value = $alias . '-' . $suffix++;
        }

        return $value;
    }

    private function aliasExists(string $alias, DataContainer $dataContainer): bool
    {
        if (! $dataContainer->activeRecord) {
            return false;
        }

        if ($this->multilingual) {
            return $this->connection
                    ->executeQuery(
                        'SELECT id FROM tl_contact_profile WHERE alias=? AND id!=? AND multilingual_language=?',
                        [
                            $alias,
                            $dataContainer->activeRecord->id,
                            $dataContainer->activeRecord->multilingual_language,
                        ]
                    )
                    ->rowCount() > 0;
        }

        return $this->connection
                ->executeQuery(
                    'SELECT id FROM tl_contact_profile WHERE alias=? AND id!=?',
                    [$alias, $dataContainer->activeRecord->id]
                )
                ->rowCount() > 0;
    }

    /**
     * @param string[] $row
     *
     * @Callback(table="tl_contact_profile", target="list.sorting.child_record")
     */
    public function generateRow(array $row): string
    {
        $label = $row['lastname'];

        if ($row['firstname']) {
            $label .= ', ' . $row['firstname'];
        }

        if ($row['alias']) {
            $label .= sprintf(' <span class="tl_gray">[%s]</span>', $row['alias']);
        }

        return $label;
    }

    /**
     * @param string|list<array<string,mixed>> $values
     *
     * @return list<array<string,mixed>>
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @Callback(table="tl_contact_profile", target="fields.videos.save")
     */
    public function saveVideos($values, DataContainer $dataContainer): array
    {
        $values = StringUtil::deserialize($values, true);

        foreach ($values as $index => $value) {
            switch ($value['videoSource']) {
                case 'youtube':
                    $values[$index]['video'] = $this->extractYouTubeId($value['video']);
                    break;
                case 'vimeo':
                    $values[$index]['video'] = $this->extractVimeoId($value['video']);
                    break;
            }
        }

        /** @psalm-suppress LessSpecificReturnStatement */
        return $values;
    }

    /**
     * Return the "toggle visibility" button
     *
     * @param string[] $row
     *
     * @Callback(table="tl_contact_profile", target="list.operations.toggle.button")
     */
    public function toggleIcon(
        array $row,
        ?string $href,
        string $label,
        string $title,
        string $icon,
        string $attributes
    ): string {
        if (Input::get('tid') !== null && Input::get('tid') !== '') {
            /** @psalm-suppress RiskyTruthyFalsyComparison */
            $this->toggleVisibility(
                (int) Input::get('tid'),
                (Input::get('state') === '1'),
                (@func_get_arg(12) ?: null)
            );
            Backend::redirect(Backend::getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        $user = BackendUser::getInstance();
        if (! $user instanceof BackendUser || ! $user->hasAccess('tl_contact_profile::published', 'alexf')) {
            return '';
        }

        $href  = (string) $href;
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
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function toggleVisibility(int $intId, bool $blnVisible, ?DataContainer $dataContainer = null): void
    {
        // Set the ID and action
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        if ($dataContainer) {
            $dataContainer->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        $callbacks = $this->dcaManager->getDefinition(Profile::getTable())->get(['config', 'onload_callback']);
        if (is_array($callbacks)) {
            foreach ($callbacks as $callback) {
                if (is_array($callback)) {
                    $callback[0] = System::importStatic($callback[0]);
                    $callback[0]->{$callback[1]}($dataContainer);
                } elseif (is_callable($callback)) {
                    $callback($dataContainer);
                }
            }
        }

        // Check the field access
        $user = BackendUser::getInstance();
        if (! $user instanceof BackendUser || ! $user->hasAccess('tl_contact_profile::published', 'alexf')) {
            throw new AccessDeniedException('Not enough permissions to publish/unpublish article ID "' . $intId . '".');
        }

        // Set the current record
        if ($dataContainer) {
            $objRow = Database::getInstance()->prepare('SELECT * FROM tl_contact_profile WHERE id=?')
                ->limit(1)
                ->execute($intId);

            if ($objRow->numRows) {
                $dataContainer->activeRecord = $objRow;
            }
        }

        $objVersions = new Versions('tl_contact_profile', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        $callbacks = $this->dcaManager
            ->getDefinition(Profile::getTable())
            ->get(['fields', 'published', 'save_callback']);

        if (is_array($callbacks)) {
            foreach ($callbacks as $callback) {
                if (is_array($callback)) {
                    $callback[0] = System::importStatic($callback[0]);
                    $blnVisible  = (bool) $callback[0]->{$callback[1]}($dataContainer);
                } elseif (is_callable($callback)) {
                    $blnVisible = (bool) $callback($dataContainer);
                }
            }
        }

        $time = time();

        // Update the database
        Database::getInstance()
            ->prepare('UPDATE tl_contact_profile %s WHERE id=?')
            ->set(['tstamp' => $time, 'published' => ($blnVisible ? '1' : '')])
            ->execute($intId);

        if ($dataContainer && $dataContainer->activeRecord) {
            $dataContainer->activeRecord->tstamp    = $time;
            $dataContainer->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        $callbacks = $this->dcaManager
            ->getDefinition(Profile::getTable())
            ->get(['config', 'onsubmit_callback']);

        if (is_array($callbacks)) {
            foreach ($callbacks as $callback) {
                if (is_array($callback)) {
                    $callback[0] = System::importStatic($callback[0]);
                    $callback[0]->{$callback[1]}($dataContainer);
                } elseif (is_callable($callback)) {
                    $callback($dataContainer);
                }
            }
        }

        $objVersions->create();
    }

    /**
     * Extract the YouTube ID from an URL
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function extractYouTubeId($varValue)
    {
        $matches = [];

        if (
            preg_match(
                '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i',
                $varValue,
                $matches
            )
        ) {
            $varValue = $matches[1];
        }

        return $varValue;
    }

    /**
     * Extract the Vimeo ID from an URL
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function extractVimeoId($varValue)
    {
        $matches = [];

        if (
            preg_match(
                '%vimeo\.com/(?:channels/(?:\w+/)?|groups/(?:[^/]+)/videos/|album/(?:\d+)/video/)?(\d+)(?:$|/|\?)%i',
                $varValue,
                $matches
            )
        ) {
            $varValue = $matches[1];
        }

        return $varValue;
    }
}
