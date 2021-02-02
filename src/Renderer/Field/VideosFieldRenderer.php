<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\FilesModel;
use Contao\FrontendTemplate;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;

final class VideosFieldRenderer extends AbstractFieldRenderer
{
    protected const TEMPLATE = 'hofff_contact_field_videos';

    /** @param mixed $videos */
    protected function compile(FrontendTemplate $template, $videos, ContactProfileRenderer $renderer): void
    {
        \dump($videos);
        $template->value = array_filter(
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
                (array) $videos
            ),
            static function (array $video): bool {
                \dump($video);
                return !empty($video['url']);
            }
        );
    }
}
