<?php
namespace Nzxt\Renderer;

use Nzxt\Controller\FrontendController;
use Nzxt\Model\Node;
use Signature\Mvc\Routing\LinkBuilder;
use Signature\Mvc\View\PhpView;

/**
 * Class NodeRenderer
 * @package Nzxt\Renderer
 */
class NodeRenderer
{
    use \Nzxt\Service\Auth\AuthServiceTrait;
    use \Signature\Object\ObjectProviderServiceTrait;

    /*
     * @var int
     */
    protected $renderedNodesCount = 0;

    /**
     * Renders a node by calling the render-method of its content.
     * @param Node $node
     * @return string
     */
    public function render(Node $node): string
    {
        $node->setRenderer($this);

        if ($this->authService->isAuthenticated()) {
            /** @var PhpView $view */
            $view = $this->objectProviderService->create(PhpView::class);

            /** @var LinkBuilder $linkBuilder */
            $linkBuilder = $this->objectProviderService->create(LinkBuilder::class);

            return $view
                ->setViewData('renderedNodesCount', ++$this->renderedNodesCount)
                ->setViewData('node', $node)
                ->setViewData('user', $this->authService->getCurrentUser())
                ->setViewData('currentNode', FrontendController::getCurrentNode())
                ->setViewData('linkBuilder', $linkBuilder)
                ->setTemplate('modules/Nzxt/tpl/Templates/NodeRenderer/Render.phtml')
                ->render();
        } else {
            return ($content = $node->getContent()) ? $content->render() : '';
        }
    }

    /**
     * @return int
     */
    public function getRenderedNodesCount(): int
    {
        return $this->renderedNodesCount;
    }
}
