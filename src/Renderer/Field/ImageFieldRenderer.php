<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;
use function is_file;

final class ImageFieldRenderer extends AbstractFieldRenderer
{
    protected const TEMPLATE = 'hofff_contact_field_image';

    /** @var string */
    private $projectDir;

    public function __construct(ContaoFrameworkInterface $framework, string $projectDir)
    {
        parent::__construct($framework);

        $this->projectDir = $projectDir;
    }

    protected function compile(FrontendTemplate $template, $value, ContactProfileRenderer $renderer): void
    {
        /** @var FilesModel $model */
        $model = $this->framework->getAdapter(FilesModel::class)->findByUuid($value);
        if (!$model || !is_file($this->projectDir . '/' . $model->path)) {
            return;
        }

        $image = [
            'singleSRC' => $model->path,
            'size'      => $renderer->imageSize()
        ];

        $template->caption = $template->profile['caption'];

        $this->framework->getAdapter(Controller::class)->addImageToTemplate($template, $image);
    }

}
