<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;

use function is_file;

final class ImageFieldRenderer extends AbstractFieldRenderer
{
    /** @var string|null */
    protected $template = 'hofff_contact_field_image';

    /** @var string */
    private $projectDir;

    public function __construct(ContaoFramework $framework, string $projectDir)
    {
        parent::__construct($framework);

        $this->projectDir = $projectDir;
    }

    /** @param mixed $value */
    protected function compile(FrontendTemplate $template, $value, ContactProfileRenderer $renderer): void
    {
        $model = $this->framework->getAdapter(FilesModel::class)->findByUuid($value);
        if (! $model instanceof FilesModel || ! is_file($this->projectDir . '/' . $model->path)) {
            return;
        }

        $image = [
            'singleSRC' => $model->path,
            'size'      => $renderer->imageSize(),
        ];

        $this->framework->getAdapter(Controller::class)->addImageToTemplate($template, $image, null, null, $model);

        if (! $template->profile['caption']) {
            return;
        }

        $template->caption = $template->profile['caption'];
    }
}
