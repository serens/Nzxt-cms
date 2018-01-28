<?php
namespace Nzxt\Model;

use Nzxt\Model\Content\AbstractContent;
use Nzxt\Renderer\NodeRenderer;
use Signature\Object\ObjectProviderService;
use Signature\Persistence\ActiveRecord\RecordInterface;

/**
 * Class Node
 * @package Nzxt\Model
 */
class Node extends AbstractModel
{
    use \Nzxt\Service\Auth\AuthServiceTrait;

    /**
     * @var Node
     */
    protected $parent = null;

    /**
     * @var array
     */
    protected $parents = null;

    /**
     * @var array
     */
    protected $children = [];

    /**
     * @var AbstractContent
     */
    protected $content = null;

    /**
     * @var NodeRenderer
     */
    protected $renderer = null;

    /**
     * Returns the parent node of this node if one exists.
     * @return Node|null
     */
    public function getParent()
    {
        if (null === $this->parent) {
            if (($nodes = self::findByField($this->primaryKeyName, (int) $this->getFieldValue('pid'))) && $nodes->count()) {
                $this->parent = $nodes->getFirst();
            }
        }

        return $this->parent;
    }

    /**
     * Returns all parent nodes up to the root node.
     * @return array
     */
    public function getParents(): array
    {
        if (null === $this->parents) {
            $parents = [];
            $node    = $this;

            while ($parent = $node->getParent()) {
                $parents[] = $parent;
                $node = $parent;
            }

            $this->parents = $parents;
        }

        return $this->parents;
    }

    /**
     * Returns the child nodes of this node.
     *
     * The returned array of this method will be cached. Thus, multiple calls to getChildren() with the same arguments
     * will return the cached array.
     * @param string $section If given, only sub nodes in this section will be retrieved.
     * @param array $types If given, only nodes of this type will be retrieved.
     * @return array
     */
    public function getChildren(string $section = '', array $types = []): array
    {
        if (!array_key_exists($cacheIdentifier = md5(serialize(array($section, $types))), $this->children)) {
            $where = ['pid = ' . $this->getID()];

            if ('' !== $section) {
                $where[] = 'section = ' . $this->persistenceService->quote($section);
            }

            if (!$this->authService->isAuthenticated()) {
                $where[] = 'hidden = 0';
            }

            if (count($types)) {
                $types = array_map(function($value) {
                    return $this->persistenceService->quote($value);
                }, $types);

                $where[] = 'content_classname IN (' . implode(', ', $types) . ')';
            }

            if ($children = self::findByQuery('*', implode(' AND ', $where), 'sort')) {
                $this->children[$cacheIdentifier] = $children->toArray();
            }
        }

        return $this->children[$cacheIdentifier];
    }

    /**
     * Returns already rendered sub nodes of this node.
     * @param string $section
     * @param array $types
     * @return string
     */
    public function getRenderedChildren(string $section = '', array $types = []): string
    {
        $content = '';

        /** @var Node $child */
        foreach ($this->getChildren($section, $types) as $child) {
            $content .= $this->getRenderer()->render($child);
        }

        return $content;
    }

    /**
     * Returns the content of a node.
     * @return AbstractContent|null
     */
    public function getContent()
    {
        if (null === $this->content) {
            if ($reference = $this->getReferenceNode()) {
                $this->content = $reference->getContent();

                return $this->content;
            }

            if ($contentClassname = $this->getFieldValue('content_classname')) {
                try {
                    /** @var AbstractContent $contentClass */
                    $contentClass = ObjectProviderService::getInstance()->get($contentClassname);
                    $this->content = $contentClass;

                    if (($result = $contentClass::findByField('node_id', $this->getID())) && $result->count()) {
                        $this->content = $result->getFirst();
                    }
                } catch (\RuntimeException $e) {
                }

                $this->content->setNode($this);
            }
        }

        return $this->content;
    }

    /**
     * Gets the reference node of this node if a reference node is defined.
     * @return Node|null
     */
    public function getReferenceNode()
    {
        if ($refNodeId = (int) $this->getFieldValue('reference')) {
            return Node::find($refNodeId);
        }
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->getFieldValue('title');
    }

    /**
     * Returns a css-classname of an icon.
     * @return string
     */
    public function getIcon(): string
    {
        return ($content = $this->getContent()) ? $content->getIcon() : 'fa fa-cube';
    }

    /**
     * Save this node and its assigned content.
     * @return RecordInterface
     */
    public function save(): RecordInterface
    {
        if ($content = $this->getContent()) {
            $content->save();
        }

        return parent::save();
    }

    /**
     * Delete the node and its assigned content.
     *
     * The content will only be deleted if this node is not a reference node. The content of the original node
     * will not be deleted.
     *
     * Calling delete() on a node causes every child node to be deleted as well.
     * @return void
     */
    public function delete()
    {
        if (null === $this->getReferenceNode() && ($content = $this->getContent())) {
            $content->delete();
        }

        if ($children = $this->getChildren()) {
            /** @var Node $child */
            foreach ($children as $child) {
                $child->delete();
            }
        }

        parent::delete();
    }

    /**
     * Resets the cached content in case a new content class name is assigned.
     * @param string $field
     * @param mixed $value
     * @return RecordInterface
     */
    public function setFieldValue(string $field, $value): RecordInterface
    {
        if ('content_classname' === strtolower($field)) {
            $this->content = null;
        }

        return parent::setFieldValue($field, $value);
    }

    /**
     * Sets the value of $renderer.
     * @param NodeRenderer $renderer
     * @return Node
     */
    public function setRenderer(NodeRenderer $renderer): Node
    {
        $this->renderer = $renderer;
        return $this;
    }

    /**
     * Returns the value of $renderer.
     * @return NodeRenderer
     */
    public function getRenderer(): NodeRenderer
    {
        return $this->renderer;
    }
}
