<?php
namespace Application\Controller;

use Nzxt\Controller\PluginController;

/**
 * Class MyFirstPluginController
 * @package Application\Controller
 */
class MyFirstPluginController extends PluginController
{
    /**
     * Indexaction for this controller.
     * @return string
     */
    public function indexAction(): string
    {
        return 'This is the content of a snippet controller.';
    }
}
