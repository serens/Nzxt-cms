<?php
namespace Nzxt\Controller;

use Nzxt\Model\Node;

/**
 * Class SitemapController
 * @package Nzxt\Controller
 */
class SitemapController extends AbstractBackendController
{
    /**
     * @param int $node
     * @return void
     */
    public function indexAction(int $node)
    {
        // Check if given node-id is valid and the node can be loaded
        $startNode = $node ? Node::find($node) : null;

        $this->view->setViewData(['startNode' => $startNode]);
    }

    /**
     * Loads children of a given node and returns the children as a json object.
     * @return string
     */
    public function childrenAction(): string
    {
        $parentId = $this->request->hasParameter('parent') ? $this->request->getParameter('parent') : 0;

        /** @var Node $parent */
        $parent   = Node::find($parentId);
        $result   = ['sections' => [], 'pid' => $parentId];
        $content  = $parent ? $parent->getContent() : null;
        $sections = $content && $content->getAvailableSectionNames()
            ? $content->getAvailableSectionNames()
            : [''];

        foreach ($sections as $section) {
            $result['sections'][$section] = [];

            $children = is_object($parent)
                ? $parent->getChildren($section)
                : Node::findByQuery('*', 'pid=0', 'sort')->toArray();

            /** @var Node $child */
            foreach ($children as $child) {
                $moreChildren = $child->getChildren();

                $result['sections'][$section][] = [
                    'id' => $child->getID(),
                    'uri' => $this->linkBuilder->build('node:view', ['#node' => $child->getID()]),
                    'icon' => $child->getIcon(),
                    'title' => $child->getTitle(),
                    'hasChildren' => count($moreChildren) > 0,
                    'isDropTarget' => ($content = $child->getContent()) && $content->canHaveChildNodes(),
                    'isMovable' => $child->getID() !== 0,
                ];
            }
        }

        return json_encode($result);
    }

    /**
     * Changes the parent (target) and section (target) of a given node (source).
     * @return string
     */
    public function moveAction(): string
    {
        if ($this->request->isPost()) {
            $source = (int) $this->request->getParameter('source');
            $target = (string) $this->request->getParameter('target');
            $section = '';

            if (false !== strpos($target, ':')) {
                list($target, $section) = explode(':', $target);
            }

            try {
                $node = $this->loadNode($source);
                $target = $this->loadNode((int) $target); // Load target node to trigger exception if target does not exist.

                $node->setFieldValues([
                    'pid' => $target->getID(),
                    'section' => $section,
                ])->save();

                $status = 'ok';
            } catch (\Nzxt\Exception\Node\NodeNotFoundException $e) {
                $status = 'failed';
            }

            return json_encode(['status' => $status]);
        }
    }
}
