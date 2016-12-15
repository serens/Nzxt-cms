<?php
/**
 * This file is part of the Signature MVC-Framework.
 * @copyright Sven Erens <sven@signature-framework.com>
 */

namespace Application;

/**
 * Class Module
 * @package Application
 */
class Module extends \Signature\Module\AbstractModule
{
    /**
     * @var string
     */
    protected $author = 'Sven Erens <sven@signature-framework.com>';

    /**
     * @var string
     */
    protected $copyright = 'Copyright &copy; 2014';

    /**
     * @var string
     */
    protected $version = '0.1 Alpha';

    /**
     * @var string
     */
    protected $description = 'This is a simple application for getting started with the Signature MVC-Framework.';

    /**
     * @var string
     */
    protected $url = 'http://www.signature-framework.com/';

    /**
     * Adds routing information to the Signature Configuration.
     * @return bool
     */
    public function init(): bool
    {
        $this->configurationService->setConfigByPath('Nzxt', 'RootNodeId', 3);

        $this->configurationService->setConfigByPath(
            'Signature',
            'Mvc.Routing.Matcher.Signature\\Mvc\\Routing\\Matcher\\UriMatcher.Routes',
            [
                'home' => [
                    'Uris'                => ['/'],
                    'ControllerClassname' => \Application\Controller\IndexController::class,
                    'ActionName'          => 'index',
                ],
            ]
        );

        $this->configurationService->setConfigByPath(
            'Signature',
            'Service.Persistence.ConnectionInfo',
            [
                'Host'     => 'localhost',
                'Username' => 'root',
                'Password' => 'root',
                'Database' => 'nzxt_cms'
            ]
        );

        return parent::init();
    }
}
