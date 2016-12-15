<?php
namespace Nzxt;

use Nzxt\Controller\FrontendController;
use Nzxt\Controller\LoginController;
use Nzxt\Controller\NodeController;
use Nzxt\Controller\SitemapController;
use Nzxt\Service\Auth\AuthService;
use Nzxt\Service\Image\ImageService;
use Signature\Module\AbstractModule;

/**
 * Class Module
 * @package Nzxt
 */
class Module extends AbstractModule
{
    use \Signature\Object\ObjectProviderServiceTrait;

    /**
     * @var string
     */
    protected $author = 'Sven Erens <sven@signature-framework.com>';

    /**
     * @var string
     */
    protected $copyright = 'Copyright &copy; 2016';

    /**
     * @var string
     */
    protected $version = '0.1 Alpha';

    /**
     * @var string
     */
    protected $description = 'This is a simple cms implementation.';

    /**
     * @var string
     */
    protected $url = 'http://www.signature-framework.com/';

    /**
     * Adds routing information and services to the Signature Configuration.
     * @return bool
     */
    public function init(): bool
    {
        session_start();

        $this->objectProviderService
            ->registerService('AuthService', AuthService::class)
            ->registerService('ImageService', ImageService::class);

        if (null === $this->configurationService->getConfigByPath('Nzxt', 'RootNodeId')) {
            $this->configurationService->setConfigByPath('Nzxt', 'RootNodeId', 1);
        }

        if (null === $this->configurationService->getConfigByPath('Nzxt', 'UploadDirectory')) {
            $this->configurationService->setConfigByPath('Nzxt', 'UploadDirectory', 'upload');
        }

        $this->configurationService->setConfigByPath(
            'Signature',
            'Mvc.Routing.Matcher.Signature\\Mvc\\Routing\\Matcher\\UriMatcher.Routes',
            [
                'home' => [
                    'Uris'                => ['/'],
                    'ControllerClassname' => FrontendController::class,
                    'ActionName'          => 'index',
                ],
                'sitemap:ajax:children' => [
                    'Uris'                => ['/sitemap/children'],
                    'ControllerClassname' => SitemapController::class,
                    'ActionName'          => 'children',
                ],
                'sitemap:ajax:move' => [
                    'Uris'                => ['/sitemap/move'],
                    'ControllerClassname' => SitemapController::class,
                    'ActionName'          => 'move',
                ],
                'sitemap' => [
                    'Uris'                => ['/sitemap/#node'],
                    'ControllerClassname' => SitemapController::class,
                    'ActionName'          => 'index',
                ],
                'node:edit' => [
                    'Uris'                => ['/node/#node/edit'],
                    'ControllerClassname' => NodeController::class,
                    'ActionName'          => 'edit',
                ],
                'node:add' => [
                    'Uris'                => ['/node/#node/add'],
                    'ControllerClassname' => NodeController::class,
                    'ActionName'          => 'add',
                ],
                'node:delete' => [
                    'Uris'                => ['/node/#node/delete'],
                    'ControllerClassname' => NodeController::class,
                    'ActionName'          => 'delete',
                ],
                'node:sort' => [
                    'Uris'                => ['/node/#node/sort'],
                    'ControllerClassname' => NodeController::class,
                    'ActionName'          => 'sort',
                ],
                'node:info' => [
                    'Uris'                => ['/node/#node/info'],
                    'ControllerClassname' => NodeController::class,
                    'ActionName'          => 'info',
                ],
                'node:view' => [
                    'Uris'                => ['/node/#node'],
                    'ControllerClassname' => FrontendController::class,
                    'ActionName'          => 'render',
                ],
                'auth:login' => [
                    'Uris'                => ['/login'],
                    'ControllerClassname' => LoginController::class,
                    'ActionName'          => 'login',
                ],
                'auth:logout' => [
                    'Uris'                => ['/logout'],
                    'ControllerClassname' => LoginController::class,
                    'ActionName'          => 'logout',
                ],
            ]
        );

        return parent::init();
    }
}
