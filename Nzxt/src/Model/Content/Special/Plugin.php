<?php
namespace Nzxt\Model\Content\Special;

use Nzxt\Controller\PluginController;
use Nzxt\Model\Content\AbstractContent;
use Signature\Html\Form\Element\Input;
use Signature\Mvc\Request;
use Signature\Mvc\Response;

/**
 * Class Plugin
 * @package Nzxt\Model\Content\Special
 */
class Plugin extends AbstractContent
{
    use \Signature\Object\ObjectProviderServiceTrait;

    protected $icon = 'fa fa-plug';

    protected $description = 'A content type which allows to select a controller action to render content.';

    /**
     * Description of the field elements.
     * @var array
     */
    protected $fieldDescription = [
        'controller_name' => [
            'elementClassname' => Input::class,
            'label' => 'Controller',
        ],
        'action_name' => [
            'elementClassname' => Input::class,
            'label' => 'Action',
        ],
    ];

    /**
     * Calls the configured content controller and returns its result.
     * @return string
     * @throws \InvalidArgumentException
     */
    public function render(): string
    {
        /** @var Response $response */
        $response = $this->objectProviderService->get(Response::class);

        /** @var Request $request */
        $request = $this->objectProviderService->get(Request::class);

        /** @var PluginController $controller */
        $controller = $this->objectProviderService->get($this->getFieldValue('controller_name'));

        if (!$controller instanceof PluginController) {
            throw new \InvalidArgumentException(sprintf(
                'Selected controller must be instance of "%s".',
                PluginController::class
            ));
        }

        $request
            ->setControllerActionName($this->getFieldValue('action_name'))
            ->setRequestUri($_SERVER['REQUEST_URI'])
            ->setParameters(isset($_REQUEST) ? $_REQUEST : []);

        $controller
            ->setPlugin($this)
            ->handleRequest($request, $response);

        return $response->getContent();
    }
}
