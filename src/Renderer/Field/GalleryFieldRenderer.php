<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\File;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\Model\Collection;
use Contao\StringUtil;
use Exception;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;
use stdClass;

use function array_filter;
use function array_flip;
use function array_key_exists;
use function array_map;
use function array_merge;
use function array_values;
use function file_exists;
use function is_array;
use function trim;
use function uniqid;

final class GalleryFieldRenderer extends AbstractFieldRenderer
{
    /** @var string|null */
    protected $template = 'hofff_contact_field_gallery';

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
        $images          = $this->fetchImagesOrderedByCustomOrder((array) $value, $template->profile);
        $template->value = $this->compileImages($images, $renderer->imageSize());
    }

    /**
     * Apply custom sorting.
     *
     * @param list<string>        $uuids
     * @param array<string,mixed> $profile
     *
     * @return list<array<string,mixed>>
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function fetchImagesOrderedByCustomOrder(array $uuids, array $profile): array
    {
        $collection = FilesModel::findMultipleByUuids($uuids);
        if (! $collection instanceof Collection) {
            return [];
        }

        $images = $this->prepareFiles($collection);
        $tmp    = StringUtil::deserialize($profile['galleryOrder']);

        if (empty($tmp) || ! is_array($tmp)) {
            return $images;
        }

        // Remove all values
        $ordered = array_map(
            /** @param mixed $value */
            static function ($value): void {
            },
            array_flip($tmp)
        );

        // Move the matching elements to their position in $order
        foreach ($images as $k => $v) {
            if (! array_key_exists($v['uuid'], $ordered)) {
                continue;
            }

            $ordered[$v['uuid']] = $v;
            unset($images[$k]);
        }

        // Append the left-over images at the end
        if (! empty($images)) {
            $ordered = array_merge($ordered, array_values($images));
        }

        // Remove empty (unreplaced) entries
        $images = array_values(array_filter($ordered));
        unset($ordered);

        return $images;
    }

    /**
     * Prepare all file data and return the aux dates.
     *
     * @param Collection                        $collection File model collection.
     * @param array<string,array<string,mixed>> $images     The collected images.
     * @param bool                              $deep       If true sub files are added as well.
     *
     * @return list<array<string,mixed>>
     *
     * @throws Exception If file could not be opened.
     */
    protected function prepareFiles(Collection $collection, array $images = [], bool $deep = true): array
    {
        // Get all images
        foreach ($collection as $fileModel) {
            // Continue if the files has been processed or does not exist
            if (isset($images[$fileModel->path]) || ! file_exists($this->projectDir . '/' . $fileModel->path)) {
                continue;
            }

            if ($fileModel->type === 'file') {
                // Single files
                $file = new File($fileModel->path);

                if (! $file->isImage) {
                    continue;
                }

                // Add the image
                $images[$fileModel->path] = [
                    'id'         => $fileModel->id,
                    'uuid'       => $fileModel->uuid,
                    'name'       => $file->basename,
                    'singleSRC'  => $fileModel->path,
                    'title'      => StringUtil::specialchars($file->basename),
                    'filesModel' => $fileModel->current(),
                ];
            } elseif ($deep) {
                // Folders
                $subfiles = FilesModel::findByPid($fileModel->uuid);

                if ($subfiles instanceof Collection) {
                    $images = $this->prepareFiles($subfiles, $images, false);
                }
            }
        }

        return array_values($images);
    }

    /**
     * @param list<array<string,mixed>> $images
     * @param list<string>|null         $imageSize
     *
     * @return list<stdClass>
     */
    private function compileImages(array $images, ?array $imageSize): array
    {
        $compiled   = [];
        $lightBoxId = 'lightbox[lb' . uniqid() . ']';

        foreach ($images as $index => $image) {
            $cell        = new FrontendTemplate();
            $cell->class = 'image_' . $index;

            // Add size and margin
            $image['size'] = $imageSize;

            Controller::addImageToTemplate(
                $cell,
                $image,
                null,
                $lightBoxId,
                $image['filesModel']
            );

            if ($cell->picture['class']) {
                $cell->picture['class'] = trim($cell->picture['class']);
            }

            $compiled[] = (object) $cell->getData();
        }

        return $compiled;
    }
}
