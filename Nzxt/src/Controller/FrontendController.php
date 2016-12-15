<?php
namespace Nzxt\Controller;

use Nzxt\Model\Node;
use Nzxt\Renderer\NodeRenderer;
use Signature\Mvc\Controller\ActionController;

/**
 * Class FrontendController
 * @package Nzxt\Controller
 */
class FrontendController extends ActionController
{
    use \Nzxt\Service\Auth\AuthServiceTrait;

    /**
     * The currently renderend main node.
     * @var Node
     */
    static protected $currentNode = null;

    /**
     * Indexaction for this controller.
     * Will be called when no node is given. In most cases this is the homepage.
     * @return void
     */
    public function indexAction()
    {
        $this->forward('render');
    }

    /**
     * Renders a single node identified by a node id.
     * @param int $nodeId
     * @return string
     */
    public function renderAction(int $nodeId = null): string
    {
        if (null === $nodeId) {
            $nodeId = $this->configurationService->getConfigByPath('Nzxt', 'RootNodeId');
        }

        /** @var Node $node */
        $node = Node::find($nodeId);

        /** @var NodeRenderer $renderer */
        $renderer = $this->objectProviderService->create(NodeRenderer::class);

        // Make current node visible in a global scope
        self::setCurrentNode($node);

        return $renderer->render($node);
    }

    /**
     * Sets the top node which is beeing rendered.
     *
     * The content of such a node is typically a Page.
     * @param Node $node
     * @return void
     */
    static public function setCurrentNode(Node $node)
    {
        self::$currentNode = $node;
    }

    /**
     * Return the node which is beeing rendered.
     * @return Node
     */
    static public function getCurrentNode(): Node
    {
        return self::$currentNode;
    }
}
