<?php
namespace Nzxt\Service\Image;

/**
 * Trait ImageServiceTrait
 * @package Nzxt\Service\Image
 */
trait ImageServiceTrait
{
    /**
     * @var \Nzxt\Service\Image\ImageService
     */
    protected $imageService;

    /**
     * Sets the Image-Service via Dependency Injection.
     * @param \Nzxt\Service\Image\ImageService $imageService
     * @return void
     */
    public function setAuthService(\Nzxt\Service\Image\ImageService $imageService)
    {
        $this->imageService = $imageService;
    }
}
