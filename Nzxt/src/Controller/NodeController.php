<?php
namespace Nzxt\Controller;

use Nzxt\Form\Element\ContentType;
use Nzxt\Model\Node;
use Signature\Html\Form\Element\Checkbox;
use Signature\Html\Form\Element\Hidden;
use Signature\Html\Form\Element\Input;
use Signature\Html\Form\Element\Select;
use Signature\Html\Form\Form;

/**
 * Class NodeController
 * @package Nzxt\Controller
 */
class NodeController extends AbstractBackendController
{
    /**
     * @param int $nodeId
     * @return void
     */
    public function editAction(int $nodeId)
    {
        $node = $this->loadNode($nodeId);
        $sections = $this->retrieveAvailableSectionNamesOfNode(($parent = $node->getParent()) ? $parent : $node);

        // Basic fields for the node itself
        $form = new Form($this->request, [], [
            new Input('title', $node->getFieldValue('title')),
            new ContentType('content_classname', $node->getFieldValue('content_classname')),
            new Select('section', $node->getFieldValue('section'), [], $sections),
            new Checkbox('hidden', $node->getFieldValue('hidden')),
            (new \Nzxt\Form\Element\Node('reference', $node->getFieldValue('reference')))->setResetable(true),
            (new \Nzxt\Form\Element\Node('pid', $node->getFieldValue('pid')))->setParentCheck(true),
        ]);

        // The fields of the node content
        if ($content = $node->getContent()) {
            $form->addElements($content->getScaffoldedFormElements());
        }

        if ($this->request->isPost()) {
            // Update or change content type
            if ($content && $form->getElementValue('content_classname') == $node->getFieldValue('content_classname')) {
                foreach (array_keys($content->getScaffoldedFormElements()) as $elementName) {
                    $content->setFieldValue($elementName, $form->getElementValue($elementName));
                }

                $content->save();
            } else {
                $node->setFieldValue('content_classname', $form->getElementValue('content_classname'));

                if ($form->getElementValue('content_classname')) {
                    // Different content type selected: Is there already an existing content type entity available?
                    if (!$node->getContent()) {
                        /** @var \Nzxt\Model\Content\AbstractContent $newContent */
                        if ($newContent = $this->objectProviderService->get($form->getElementValue('content_classname'))) {
                            $newContent
                                ->setFieldValue('node_id', $node->getID())
                                ->create();
                        }
                    }
                }
            }

            $node
                ->touch()
                ->setFieldValues([
                    'modifier_id' => $this->authService->getCurrentUser()->getNode()->getID(),
                    'title' => $form->getElementValue('title'),
                    'reference' => (int) $form->getElementValue('reference'),
                    'section' => $form->getElementValue('section'),
                    'pid' => (int) $form->getElementValue('pid'),
                    'hidden' => (int) $form->getElementValue('hidden'),
                ])
                ->save();

            return $this->closeDialog();
        } else {
            $this->view->setViewData(['node' => $node, 'form' => $form]);
        }
    }

    /**
     * @param int $nodeId
     * @return void
     */
    public function deleteAction(int $nodeId)
    {
        $node = $this->loadNode($nodeId);

        // Basic fields for the node itself
        $form = new Form($this->request);

        if ($this->request->isPost()) {
            $node->delete();
            return $this->closeDialog();
        } else {
            $this->view->setViewData(['node' => $node, 'form' => $form]);
        }
    }

    /**
     * @param int $nodeId
     * @return void
     */
    public function addAction(int $nodeId)
    {
        $node = $this->loadNode($nodeId);
        $sections = $this->retrieveAvailableSectionNamesOfNode($node);

        // Basic fields for the node itself
        $form = new Form($this->request, [], [
            new Input('title', 'A new node'),
            new ContentType('content_classname', '', [], $node),
            new Select('section', $node->getFieldValue('section'), [], $sections),
            new Checkbox('hidden', '1'),
            (new \Nzxt\Form\Element\Node('reference', ''))->setResetable(true),
        ]);

        if ($this->request->isPost()) {
            /**
             * Create the new node.
             * @var Node $newNode
             */
            $newNode = $this->objectProviderService->get(Node::class);
            $newNode
                ->setFieldValues([
                    'pid' => $node->getID(),
                    'created' => date('Y-m-d H:i:s'),
                    'creator_id' => $this->authService->getCurrentUser()->getNode()->getID(),
                    'title' => $form->getElementValue('title'),
                    'reference' => (int) $form->getElementValue('reference'),
                    'content_classname' => $form->getElementValue('content_classname'),
                    'section' => $form->getElementValue('section'),
                    'hidden' => (int) $form->getElementValue('hidden'),
                ])
                ->create();

            // Create new content, if content type has been selected
            if ($contentClassname = $form->getElementValue('content_classname')) {
                /** @var \Nzxt\Model\Content\AbstractContent $newContent */
                if ($newContent = $this->objectProviderService->get($contentClassname)) {
                    $newContent
                        ->setFieldValue('node_id', $newNode->getID())
                        ->create();
                }
            }

            $this->view->setViewData(['newNode' => $newNode, 'form' => $form]);
        } else {
            $this->view->setViewData(['node' => $node, 'form' => $form]);
        }
    }

    /**
     * @param int $nodeId
     * @return void
     */
    public function infoAction(int $nodeId)
    {
        $node = $this->loadNode($nodeId);
        $creator = null;
        $modifier = null;

        if ($id = (int) $node->getFieldValue('creator_id')) {
            $creator = Node::find($id);
        }

        if ($id = (int) $node->getFieldValue('modifier_id')) {
            $modifier = Node::find($id);
        }

        $this->view->setViewData([
            'node' => $node,
            'references' => Node::findByQuery('*', 'reference=' . $node->getID())->toArray(),
            'creator' => $creator,
            'modifier' => $modifier,
        ]);
    }

    /**
     * @param int $nodeId
     * @return void
     */
    public function sortAction(int $nodeId)
    {
        $node = $this->loadNode($nodeId);
        $form = new Form($this->request, [], [new Hidden('sorting')]);

        // Get siblings of the current node in the same section
        $siblings = Node::findByQuery(
            '*',
            'pid = ' . (int) $node->getFieldValue('pid') . ' AND section = "' . $node->getFieldValue('section') . '"',
            'sort'
        );

        if ($this->request->isPost()) {
            if ($form->getElementValue('sorting')) {
                $sortedSiblingIds = explode(',', $form->getElementValue('sorting'));

                /** @var \Nzxt\Model\Node $sibling */
                foreach ($siblings as $sibling) {
                    $sibling
                        ->touch()
                        ->setFieldValue('sort', array_search($sibling->getID(), $sortedSiblingIds) * 10)
                        ->save();
                }
            }

            return $this->closeDialog(true);
        } else {
            $this->view->setViewData(['siblings' => $siblings, 'form' => $form]);
        }
    }

    /**
     * Returns the available section names of a given node.
     * @param Node $node
     * @return array
     */
    protected function retrieveAvailableSectionNamesOfNode(Node $node): array
    {
        if ($content = $node->getContent()) {
            $sections = [];

            foreach ($content->getAvailableSectionNames() as $sectionName) {
                $sections[$sectionName] = $sectionName;
            }
        }

        if (0 === count($sections)) {
            $sections = ['' => '(No sections available)'];
        }

        return $sections;
    }
}
