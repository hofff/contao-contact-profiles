<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Hofff\Contao\Consent\Bridge\ConsentToolManager;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;

use function array_filter;
use function array_map;
use function str_replace;

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
    protected function hasValue($value): bool
    {
        if (! parent::hasValue($value)) {
            return false;
        }

        $profiles = array_filter(
            (array) $value,
            static function (array $config) {
                return $config['videoSource'] !== '' && $config['video'] !== '';
            }
        );

        return $profiles !== [];
    }

    /** @param mixed $value */
    protected function compile(FrontendTemplate $template, $value, ContactProfileRenderer $renderer): void
    {
        $template->renderer = $renderer;
        $template->value    = array_filter(
            array_map(
                static function (array $video) {
                    $video['aspect'] = str_replace(':', '', $video['aspect']);

                    switch ($video['videoSource']) {
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
                return ! empty($video['url']);
            }
        );

        $template->renderVideo = static function (array $video): string {
            $template = new FrontendTemplate('hofff_contact_video_' . $video['videoSource']);
            $template->setData($video);

            return $template->parse();
        };
    }
}
