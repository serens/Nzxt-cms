<?php
namespace Nzxt\Service\Image;

use Nzxt\Model\Content\Generic\File;

/**
 * Class ImageService
 * @package Nzxt\Service\Image
 */
class ImageService extends \Signature\Service\AbstractInjectableService
{
    use \Signature\Object\ObjectProviderServiceTrait;

    /**
     * @var int
     */
    protected $quality = 100;

    /**
     * @var string
     */
    const PROCESSED_FOLDER = '_processed';

    /**
     * @var int
     */
    const ORIENTATION_SQUARE = 0;

    /**
     * @var int
     */
    const ORIENTATION_PORTRAIT = 1;

    /**
     * @var int
     */
    const ORIENTATION_LANDSCAPE = 2;

    /**
     * @var int
     */
    const DIMENSION_WIDTH = 0;

    /**
     * @var int
     */
    const DIMENSION_HEIGHT = 1;

    /**
     * @var int
     */
    const DIMENSION_BOTH = null;

    /**
     * Sets the image quality in which images are created.
     * @param int $quality
     * @return ImageService
     */
    public function setQuality(int $quality): ImageService
    {
        $quality = max(1, $quality);
        $quality = min(100, $quality);

        $this->quality = $quality;

        return $this;
    }

    /**
     * Gets the current image quality.
     * @return int
     */
    public function getQuality(): int
    {
        return $this->quality;
    }

    /**
     * Applies a resize transformation on the media file.
     * @param File $file
     * @param int $width
     * @param int $height
     * @return string
     * @throws \RuntimeException
     */
    public function resize(File $file, int $width, int $height): string
    {
        $transformationCode = sprintf('resized_%d_%d', $width, $height);

        if (file_exists($processedFilename = $this->buildProcessedFilename($file, $transformationCode))) {
            return DIRECTORY_SEPARATOR . $processedFilename;
        }

        if (!$resource = $this->createResource($file)) {
            throw new \RuntimeException('Cannot create resource for file "' . $file->getPhysicalFilename() . '".');
        }

        $newResource = imagecreatetruecolor($width, $height);

        imagealphablending($newResource, false);
        imagesavealpha($newResource, true);
        imagecopyresampled($newResource, $resource, 0, 0, 0, 0, $width, $height, $this->getWidth($file), $this->getHeight($file));

        return $this->createProcessedFile($file, $newResource, $transformationCode);
    }

    /**
     * Changes the width of an image.
     * @param File $file
     * @param int $width
     * @return string
     */
    public function resizeWidth(File $file, int $width): string
    {
        return $this->resize($file, $width, (int) round(($width / $this->getWidth($file)) * $this->getHeight($file)));
    }

    /**
     * Changes the height of an image.
     * @param File $file
     * @param int $height
     * @return string
     */
    public function resizeHeight(File $file, $height): string
    {
        return $this->resize($file, (int) round(($height / $this->getHeight($file)) * $this->getWidth($file)), $height);
    }

    /**
     * Returns the width of the file.
     * @param File $file
     * @return int
     */
    public function getWidth(File $file): int
    {
        return $this->getDimension($file, self::DIMENSION_WIDTH);
    }

    /**
     * Returns the height of the file.
     * @param File $file
     * @return int
     */
    public function getHeight(File $file): int
    {
        return $this->getDimension($file, self::DIMENSION_HEIGHT);
    }

    /**
     * Returns a constant indicating the orientation of the image.
     * @param File $file
     * @return int
     */
    public function getOrientation(File $file): int
    {
        $width  = $this->getWidth($file);
        $height = $this->getHeight($file);

        if ($width > $height) {
            return self::ORIENTATION_LANDSCAPE;
        } elseif ($height > $width) {
            return self::ORIENTATION_PORTRAIT;
        } else {
            return self::ORIENTATION_SQUARE;
        }
    }

    /**
     * Returns the size of an image.
     * @param File $file
     * @param int
     * @return int|array
     * @throws \BadMethodCallException
     * @throws \RuntimeException
     */
    public function getDimension(File $file, $index = self::DIMENSION_BOTH)
    {
        if (!in_array($index, [self::DIMENSION_BOTH, self::DIMENSION_WIDTH, self::DIMENSION_HEIGHT])) {
            throw new \BadMethodCallException(
                'Incorrect call to ' . __METHOD__ . '(). ' .
                'Use either File::DIMENSION_BOTH, File::DIMENSION_WIDTH or File::DIMENSION_HEIGHT as 1st argument.'
            );
        }

        if (!$file->exists()) {
            throw new \RuntimeException(sprintf('File %s does not exist.', $file->getPhysicalFilename()));
        }

        if (!$file->isImage()) {
            throw new \RuntimeException('Method cannot be called on a non-image.');
        }

        if (is_array($dimension = getimagesize($file->getPhysicalFilename()))) {
            return
                ($index === null) ? $dimension : $dimension[$index];
        }
    }

    /**
     * Create a new image file when transformations are applied to an image.
     * @param File $file
     * @param resource $resource
     * @param string $transformationCode
     * @return string
     */
    protected function createProcessedFile(File $file, $resource, string $transformationCode): string
    {
        if (!file_exists($folder = $this->getProcessedFolder($file))) {
            mkdir($folder);
        }

        if (!is_resource($resource)) {
            return $file->getPhysicalFilename();
        }

        //$newUrl = $folder . DIRECTORY_SEPARATOR . $transformationCode . '_' . $this->getFieldValue('filename');
        $processedFilename = $this->buildProcessedFilename($file, $transformationCode);

        switch ($file->getFileExtension()) {
            case 'jpg':
            case 'jpeg':
            case 'jpe';
                imagejpeg($resource, $processedFilename, $this->quality);
                break;

            case 'gif':
                imagegif($resource, $processedFilename);
                break;

            case 'png':
            default:
                imagepng($resource, $processedFilename);
                break;
        }

        return $processedFilename;
    }

    /**
     * Gets the url of a cached media file.
     * @param File $file
     * @param string $transformationCode
     * @return string
     */
    protected function buildProcessedFilename(File $file, string $transformationCode): string
    {
        return
            $this->getProcessedFolder($file) .
            DIRECTORY_SEPARATOR .
            $transformationCode .
            '_' .
            pathinfo($file->getFieldValue('filename'), PATHINFO_BASENAME);
    }

    /**
     * Gets the name of the processed folder of a given file.
     * @param File $file
     * @return string
     */
    protected function getProcessedFolder(File $file): string
    {
        return pathinfo($file->getPhysicalFilename(), PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR  . self::PROCESSED_FOLDER;
    }

    /**
     * @param File $file
     * @return null|resource
     */
    protected function createResource(File $file)
    {
        if ($file->exists() && $file->isImage()) {
            switch ($file->getFileExtension()) {
                case 'jpg':
                case 'jpeg':
                case 'jpe':
                    if (function_exists('imagecreatefromjpeg')) {
                        return imagecreatefromjpeg($file->getPhysicalFilename());
                    }
                    break;

                case 'gif':
                    if (function_exists('imagecreatefromgif')) {
                        return imagecreatefromgif($file->getPhysicalFilename());
                    }
                    break;

                case 'png':
                    if (function_exists('imagecreatefrompng')) {
                        return imagecreatefrompng($file->getPhysicalFilename());
                    }
                    break;
            }
        }

        return null;
    }
}
