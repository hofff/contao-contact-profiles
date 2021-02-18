<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Hofff\Contao\Consent\Bridge\ConsentToolManager;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;

final class VideosFieldRenderer extends AbstractFieldRenderer
{
    protected const TEMPLATE = 'hofff_contact_field_videos';

    /** @var ConsentToolManager */
    private $consentToolManager;

    public function __construct(ContaoFrameworkInterface $framework, ConsentToolManager $consentToolManager)
    {
        parent::__construct($framework);

        $this->consentToolManager = $consentToolManager;
    }

    /** @param mixed $value */
    protected function compile(FrontendTemplate $template, $value, ContactProfileRenderer $renderer): void
    {
        $template->consentTool      = $this->consentToolManager->activeConsentTool();
        $template->youtubeConsentId = $renderer->consentId('youtube');
        $template->vimeoConsentId   = $renderer->consentId('vimeo');
        $template->value            = array_filter(
            array_map(
                static function (array $video) {
                    switch ($video['source']) {
                        case 'youtube':
                            $video['url'] = 'https://www.youtube-nocookie.com/embed/' . $video['video'];
                            break;

                        case 'vimeo':
                            $video['url'] = 'https://player.vimeo.com/video/' . $video['video'];
                            break;

                        case 'local':
                            $video['file'] = FilesModel::findByPath($video['video']);
                            $video['url']  = $video['file'] ? $video['file']->path : null;
                            break;
                    }

                    return $video;
                },
                (array) $value
            ),
            static function (array $video): bool {
                return !empty($video['url']);
            }
        );
    }
}
