<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;
use Override;

use function is_file;

final class ImageFieldRenderer extends AbstractFieldRenderer
{
    protected string|null $template = 'hofff_contact_field_image';

    public function __construct(
        ContaoFramework $framework,
        private string $projectDir,
        private readonly Studio $imageStudio,
    ) {
        parent::__construct($framework);
    }

    /** @param mixed $value */
    #[Override]
    protected function compile(
        FrontendTemplate $template,
        $value,
        Profile $profile,
        ContactProfileRenderer $renderer,
    ): void {
        $model = $this->framework->getAdapter(FilesModel::class)->findByUuid($value);
        if (! $model instanceof FilesModel || ! is_file($this->projectDir . '/' . $model->path)) {
            return;
        }

        $this->imageStudio->createFigureBuilder()
            ->fromFilesModel($model)
            ->setSize($renderer->imageSize())
            ->build()
            ->applyLegacyTemplateData($template);

        if (! $profile->caption) {
            return;
        }

        $template->caption = $profile->caption;
    }
}
