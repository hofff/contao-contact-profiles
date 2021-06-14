<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendTemplate;
use Contao\PageModel;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Hofff\Contao\Consent\Bridge\ConsentId;
use PDO;

use function array_key_exists;
use function array_map;

final class ContactProfileRenderer
{
    private const DEFAULT_TEMPLATE = 'hofff_contact_profile_default';

    private const DEFAULT_FIELD_TEMPLATE = 'hofff_contact_field';

    /** @var ContaoFramework */
    private $framework;

    /** @var Connection */
    private $connection;

    /** @var FieldRenderer */
    private $fieldRenderer;

    /** @var string[] */
    private $fields = [];

    /** @var string[]|null */
    private $imageSize;

    /** @var string */
    private $template = self::DEFAULT_TEMPLATE;

    /** @var string[] */
    private $fieldTemplates = [];

    /** @var string */
    private $defaultFieldTemplate;

    /** @var string */
    private $moreLabel;

    /** @var array<int|string, ?PageModel> */
    private $categoryDetailPages = [];

    /** @var array<string,ConsentId> */
    private $consentIds = [];

    public function __construct(
        FieldRenderer $fieldRenderer,
        string $moreLabel,
        Connection $connection,
        ContaoFramework $framework
    ) {
        $this->fieldRenderer        = $fieldRenderer;
        $this->moreLabel            = $moreLabel;
        $this->defaultFieldTemplate = self::DEFAULT_FIELD_TEMPLATE;
        $this->connection           = $connection;
        $this->framework            = $framework;
    }

    /** @param string[] $fields */
    public function withFields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function withTemplate(string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function withDefaultFieldTemplate(string $template): self
    {
        $this->defaultFieldTemplate = $template;

        return $this;
    }

    public function defaultFieldTemplate(): string
    {
        return $this->defaultFieldTemplate;
    }

    public function withFieldTemplate(string $field, string $template): self
    {
        $this->fieldTemplates[$field] = $template;

        return $this;
    }

    public function fieldTemplate(string $field, ?string $default = null): ?string
    {
        if (isset($this->fieldTemplates[$field])) {
            return $this->fieldTemplates[$field];
        }

        return $default ?: $this->defaultFieldTemplate;
    }

    /** @param string[] $imageSize */
    public function withImageSize(array $imageSize): self
    {
        $this->imageSize = $imageSize;

        return $this;
    }

    public function withConsentId(string $type, ConsentId $consentId): self
    {
        $this->consentIds[$type] = $consentId;

        return $this;
    }

    /** @return string[]|null */
    public function imageSize(): ?array
    {
        return $this->imageSize;
    }

    public function moreLabel(): string
    {
        return $this->moreLabel;
    }

    public function consentId(string $type): ?ConsentId
    {
        return $this->consentIds[$type] ?? null;
    }

    /** @param string[] $profile */
    public function render(array $profile): string
    {
        $template = new FrontendTemplate($this->template);
        $template->setData(
            [
                'renderer' => $this,
                'fields'   => $this->fields,
                'profile'  => array_map([StringUtil::class, 'deserialize'], $profile),
                'has'      => static function (string $field) use ($template): bool {
                    return ! empty($template->profile[$field]);
                },
            ]
        );

        return $template->parse();
    }

    public function generateDetailUrl(array $profile): ?string
    {
        if ($profile['jumpTo']) {
            $pageModel = $this->framework->getAdapter(PageModel::class)->findByPk($profile['jumpTo']);
            if ($pageModel === null) {
                return null;
            }

            return $pageModel->getFrontendUrl('/' . ($profile['alias'] ?: $profile['id']));
        }

        if (! array_key_exists($profile['pid'], $this->categoryDetailPages)) {
            $this->categoryDetailPages[$profile['pid']] = $this->fetchCategoryDetailPage((int) $profile['pid']);
        }

        if ($this->categoryDetailPages[$profile['pid']] === null) {
            return null;
        }

        return $this->categoryDetailPages[$profile['pid']]->getFrontendUrl('/' . ($profile['alias'] ?: $profile['id']));
    }

    /** @param string[] $profile */
    public function parseField(string $field, array $profile): string
    {
        $raw = StringUtil::deserialize($profile[$field] ?? null);

        return ($this->fieldRenderer)($field, $raw, $this, $profile) ?? '';
    }

    private function fetchCategoryDetailPage(int $categoryId): ?PageModel
    {
        $statement = $this->connection->executeQuery(
            'SELECT jumpTo from tl_contact_category WHERE id = :categoryId LIMIT 0,1',
            ['categoryId' => $categoryId]
        );

        $pageId = $statement->fetch(PDO::FETCH_COLUMN);
        if ($pageId === false || $pageId < 1) {
            return null;
        }

        /** @var Adapter<PageModel> $adapter */
        $adapter = $this->framework->getAdapter(PageModel::class);

        return $adapter->findByPk($pageId);
    }
}
