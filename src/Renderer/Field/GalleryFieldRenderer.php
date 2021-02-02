<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\Controller;
use Contao\File;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\Model\Collection;
use Contao\StringUtil;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;

final class GalleryFieldRenderer extends AbstractFieldRenderer
{
    protected const TEMPLATE = 'hofff_contact_field_gallery';

    /** @param mixed $value */
    protected function compile(FrontendTemplate $template, $value, ContactProfileRenderer $renderer): void
    {
        $images          = $this->fetchImagesOrderedByCustomOrder((array) $value, $template->profile);
        $template->value = $this->compileImages($images, $renderer->imageSize());
    }

    /**
     * Apply custom sorting.
     *
     * @return list<resource>
     */
    protected function fetchImagesOrderedByCustomOrder(array $uuids, array $profile): array
    {
        $collection = FilesModel::findMultipleByUuids($uuids);
        if ($collection === null) {
            return [];
        }

        $images = $this->prepareFiles($collection);
        $tmp    = StringUtil::deserialize($profile['galleryOrder']);

        if (empty($tmp) || !is_array($tmp)) {
            return $images;
        }

        // Remove all values
        $ordered = array_map(
            static function () {
            },
            array_flip($tmp)
        );

        // Move the matching elements to their position in $order
        foreach ($images as $k => $v) {
            if (array_key_exists($v['uuid'], $ordered)) {
                $ordered[$v['uuid']] = $v;
                unset($images[$k]);
            }
        }

        // Append the left-over images at the end
        if (!empty($images)) {
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
     * @param Collection $collection File model collection.
     * @param array      $images     The collected images.
     * @param bool       $deep       If true sub files are added as well.
     *
     * @return array
     *
     * @throws \Exception If file could not be opened.
     */
    protected function prepareFiles(Collection $collection, array $images = [], $deep = true): array
    {
        // Get all images
        foreach ($collection as $fileModel) {
            // Continue if the files has been processed or does not exist
            if (isset($images[$fileModel->path]) || !file_exists(TL_ROOT . '/' . $fileModel->path)) {
                continue;
            }

            if ($fileModel->type == 'file') {
                // Single files
                $file = new File($fileModel->path);

                if (!$file->isImage) {
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

                if ($subfiles !== null) {
                    $images = $this->prepareFiles($subfiles, $images, false);
                }
            }
        }

        return array_values($images);
    }

    private function compileImages(array $images, ?array $imageSize): array
    {
        $compiled   = [];
        $lightBoxId = 'lightbox[lb' . uniqid() . ']';

        foreach ($images as $index => $image) {
            $cell        = new FrontendTemplate();
            $cell->class = 'image_' . $index;

            // Build legacy size format.
            if (is_array($imageSize)) {
                $imageSize = [$imageSize['width'], $imageSize['height'], $imageSize['size']];
            }

            // Add size and margin
            $images[$index]['size'] = $imageSize;

            Controller::addImageToTemplate(
                $cell,
                $images[$index],
                null,
                $lightBoxId,
                $images[$index]['filesModel']
            );

            if ($cell->picture['class']) {
                $cell->picture['class'] = trim($cell->picture['class']);
            }

            $compiled[] = (object) $cell->getData();
        }

        return $compiled;

    }
}
