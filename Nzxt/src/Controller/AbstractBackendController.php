<?php
namespace Nzxt\Controller;

use Nzxt\Exception\Node\NodeNotFoundException;
use Nzxt\Model\Node;
use Signature\Mvc\Controller\ActionController;
use Signature\Mvc\Routing\LinkBuilder;

/**
 * Class AbstractBackendController
 * @package Nzxt\Controller
 */
class AbstractBackendController extends ActionController
{
    use \Nzxt\Service\Auth\AuthServiceTrait;

    /**
     * @var LinkBuilder
     */
    protected $linkBuilder = null;

    /**
     * @var bool
     */
    protected $requireAuthentication = true;

    /**
     * Checks if a valid session exists.
     * @return void
     */
    protected function initAction()
    {
        parent::initAction();

        $this->linkBuilder = $this->objectProviderService->create(LinkBuilder::class);

        if ($this->requireAuthentication && !$this->authService->getCurrentUser()) {
            $_SESSION['redirect'] = $this->request->getRequestUri();
            $this->redirect($this->linkBuilder->build('auth:login'));
        }
    }

    /**
     * Creates the link builder and assigns it to the view. Sets the layout as well.
     * @return void
     */
    protected function initView()
    {
        parent::initView();

        $this->view->setViewData([
            'linkBuilder' => $this->linkBuilder,
        ]);

        $this->view->setLayout($this->getTemplateDir() . '/Layouts/Backend/Dialog.phtml');
    }

    /**
     * Sends a json-string as a response which the Dialog Handler will recognize.
     * @param bool $reload
     * @return string
     */
    protected function closeDialog(bool $reload = true): string
    {
        $response = ['status' => 'ok'];

        if ($reload) {
            $response['action'] = 'reload';
        }

        return json_encode($response);
    }

    /**
     * Loads a node by a given node id.
     * @param int $nodeId
     * @return Node
     * @throws NodeNotFoundException
     */
    protected function loadNode(int $nodeId): Node
    {
        if (!$node = Node::find($nodeId)) {
            throw new NodeNotFoundException($nodeId);
        }

        return $node;
    }
}
