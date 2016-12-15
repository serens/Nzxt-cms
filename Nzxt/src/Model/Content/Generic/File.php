<?php
namespace Nzxt\Model\Content\Generic;

use Nzxt\Form\Element\FileUpload;
use Nzxt\Model\Content\AbstractContent;
use Nzxt\Service\Image\ImageService;
use Signature\Html\Form\Element\Input;
use Signature\Object\ObjectProviderService;

/**
 * Class File
 * @package Nzxt\Model\Content\Generic
 */
class File extends AbstractContent
{
    protected $icon = 'fa fa-file-o';

    protected $description = 'A file representing a physical file in the file system.';

    /**
     * Description of the field elements.
     * @var array
     */
    protected $fieldDescription = [
        'filename' => [
            'elementClassname' => FileUpload::class,
            'label' => 'Filename',
        ],
        'alternative' => [
            'elementClassname' => Input::class,
            'label' => 'Alternative',
        ],
    ];

    /**
     * Returns an icon classname based on the file extension.
     * @return string
     */
    public function getIcon(): string
    {
        if ($this->isImage()) {
            return 'fa fa-file-image-o';
        }

        if ($this->isAudio()) {
            return 'fa fa-file-audio-o';
        }

        if ($this->isVideo()) {
            return 'fa fa-file-video-o';
        }

        switch ($this->getFileExtension()) {
            case 'xls':
            case 'xlsx':
                return 'fa fa-file-excel-o';

            case 'pdf':
                return 'fa fa-file-pdf-o';

            case 'doc':
            case 'docx':
                return 'fa fa-file-word-o';

            case 'zip':
            case 'tar':
                return 'fa fa-file-archive-o';

            case 'txt':
                return 'fa fa-file-text-o';

            default:
                return $this->icon;
        }
    }

    /**
     * Returns true if the physical file has a file extension indicating an image such as '.gif' or '.jpg'.
     * @return bool
     */
    public function isImage(): bool
    {
        return in_array($this->getFileExtension(), ['svg', 'bmp', 'jpg', 'jpeg', 'gif', 'png', 'tiff']);
    }

    /**
     * Returns true if the physical file has a file extension indicating an audio file such as '.wav'.
     * @return bool
     */
    public function isAudio(): bool
    {
        return in_array($this->getFileExtension(), ['mp3', 'wav']);
    }

    /**
     * Returns true if the physical file has a file extension indicating a video such as '.mov'.
     * @return bool
     */
    public function isVideo(): bool
    {
        return in_array($this->getFileExtension(), ['mp4', 'mov']);
    }

    /**
     * Return the absolute filename of this file-object.
     * @return string
     */
    public function getPhysicalFilename(): string
    {
        if ($uri = $this->getUri()) {
            return trim($uri, '/');
        }

        return '';
    }

    /**
     * Returns the uri against the frontend.
     * @return string
     */
    public function getUri(): string
    {
        return $this->hasField('filename') ? (DIRECTORY_SEPARATOR . $this->getFieldValue('filename')) : '';
    }

    /**
     * Returns the extension of the file.
     * @return string
     */
    public function getFileExtension(): string
    {
        return ($filename = $this->getPhysicalFilename())
            ? strtolower(pathinfo($filename, PATHINFO_EXTENSION))
            : '';
    }

    /**
     * Returns the size of the physical file. If the file does not exist, FALSE is returned.
     * @return int
     */
    public function getFilesize(): int
    {
        return ($this->exists()) ? filesize($this->getPhysicalFilename()) : -1;
    }

    /**
     * Checks, if the file exists in the upload-directory.
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->getPhysicalFilename());
    }

    /**
     * @return array
     */
    public function getInformation(): array
    {
        /** @var \Nzxt\Service\Image\ImageService $imageService */
        $imageService = ObjectProviderService::getInstance()->getService('ImageService');

        $information = [
            'Size'   => $this->getFilesize() . ' Bytes',
            'Exists' => $this->exists() ? 'Yes' : 'No',
            'Folder' => pathinfo($this->getPhysicalFilename(), PATHINFO_DIRNAME),
            'Public URI' => sprintf('<a href="%1$s" target="_blank">%1$s</a>', $this->getUri()),
        ];

        if ($this->isImage()) {
            $information['Dimensions'] =
                $imageService->getDimension($this, ImageService::DIMENSION_WIDTH) .
                ' &times; ' .
                $imageService->getDimension($this, ImageService::DIMENSION_HEIGHT) . ' pixels';
        }

        return $information;
    }
}
