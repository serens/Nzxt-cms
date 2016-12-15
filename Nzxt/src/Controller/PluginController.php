<?php
namespace Nzxt\Controller;

use Nzxt\Model\Content\Special\Plugin;
use Signature\Mvc\Controller\ActionController;

/**
 * Class PluginController
 * @package Nzxt\Controller
 */
class PluginController extends ActionController
{
    /**
     * @var Plugin
     */
    protected $plugin = null;

    /**
     * Sets the calling type which is initiating this controller.
     * @param Plugin $plugin
     * @return PluginController
     */
    public function setPlugin(Plugin $plugin)
    {
        $this->plugin = $plugin;
        return $this;
    }

    /**
     * Returns the plugin which is handling this controller.
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }

    /**
     * Assigns the node type to the view.
     * @return void
     */
    public function initView()
    {
        parent::initView();

        $this->view->setViewData(['plugin' => $this->getPlugin()]);
    }
}
