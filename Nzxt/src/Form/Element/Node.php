<?php
namespace Nzxt\Form\Element;

use Signature\Html\Form\Element\Hidden;
use Signature\Object\ObjectProviderService;

/**
 * Class Node
 * @package Nzxt\Form\Element
 */
class Node extends Hidden
{
    /**
     * @var bool
     */
    protected $resetable = false;

    /**
     * @var string
     */
    protected $resetLabel = '(none)';

    /**
     * @var string
     */
    protected $noNodeSelectedLabel = '(No node selected)';

    /**
     * @var bool
     */
    protected $parentCheck = false;

    /**
     * @var int
     */
    protected $startNode = 0;

    /**
     * @var array
     */
    protected $allowedNodeTypes = [];

    /**
     * @var ObjectProviderService
     */
    protected $objectProviderService = null;

    /**
     * Constructor creates some member variables.
     * @param string $name
     * @param string $value
     * @param array $attributes
     */
    public function __construct(string $name, string $value = '', array $attributes = [])
    {
        $this->objectProviderService = ObjectProviderService::getInstance();

        parent::__construct($name, $value, $attributes);
    }

    /**
     * Renders the user input field.
     * @return string
     */
    public function render(): string
    {
        $node = \Nzxt\Model\Node::find((int) $this->getValue());
        $elementID = 'node-select-input-' . $this->getAttribute('id');
        $value = $node
            ? sprintf('<i class="%s fa-fw"></i> %s (#%d)', $node->getIcon(), htmlspecialchars($node->getTitle()), $node->getID())
            : '&nbsp;';

        $userInput = '
            <div class="user-input node-select-input" id="' . $elementID . '">' .
                parent::render() . '
                <div class="value">' . $value . '</div>
                <a class="panel-trigger" title="Click to select a node." href="#"><i class="fa fa-ellipsis-h"></i></a>
                <div class="panel">' . $this->renderSitemap($this->startNode) . '</div>
            </div>
        ';

        $javaScript = '
            <script>
                $(function() {
                    var $container = $("#' . $elementID . '");
                    var $input = $("input[type=hidden]", $container);
                    var $panel = $(".panel", $container);
                    var $valueDisplay = $(".value", $container);
                    var panelOpenedClassname = "panel-opened";

                    $input.on("update", function() {
                        var value = $(this).val();

                        if (isNaN(value) || "" == value || 0 == parseInt(value)) {
                            $valueDisplay.text("' . $this->noNodeSelectedLabel . '");
                        }
                    }).trigger("update");

                    $(".panel-trigger", $container).click(function(e) {
                        e.preventDefault();

                        $panel[0].scrollTop = 0;

                        $container.toggleClass(panelOpenedClassname);

                        $panel.slideToggle({
                            duration: 500,
                            easing: "easeOutExpo"
                        });
                    });

                    $("a", $panel).click(function(e) {
                        var $link = $(this);

                        e.preventDefault();

                        $panel.hide();
                        $container.removeClass(panelOpenedClassname);
                        $valueDisplay.html("<i class=\"" + $link.data("icon") + " fa-fw\"></i> " + $link.text() + " (#" + $link.data("node") + ")");
                        $input.val($link.data("node")).trigger("update");
                    });
                });
            </script>
        ';

        return $userInput . $javaScript;
    }

    /**
     * If resetable is true, the user can delete the current selected node in the input field.
     * @param bool $resetable
     * @return Node
     */
    public function setResetable(bool $resetable): Node
    {
        $this->resetable = $resetable;
        return $this;
    }

    /**
     * Returns true if the input field is resetable.
     * @return bool
     */
    public function getResetable(): bool
    {
        return $this->resetable;
    }

    /**
     * If true, only nodes can be selected which allow sub nodes.
     * @param bool $parentCheck
     * @return Node
     */
    public function setParentCheck(bool $parentCheck): Node
    {
        $this->parentCheck = $parentCheck;
        return $this;
    }

    /**
     * Returns true if only nodes can be selected which allow sub nodes.
     * @return bool
     */
    public function getParentCheck(): bool
    {
        return $this->parentCheck;
    }

    /**
     * Sets the root node of the sitemap.
     * @param int $startNode
     * @return Node
     */
    public function setStartNode(int $startNode): Node
    {
        $this->startNode = $startNode;
        return $this;
    }

    /**
     * Returns the current root node of the sitemap.
     * @return int
     */
    public function getStartNode(): int
    {
        return $this->startNode;
    }

    /**
     * Sets an array with allowed node types to be available in the sitemap.
     * @param array $allowedNodeTypes
     * @return Node
     */
    public function setAllowedNodeTypes(array $allowedNodeTypes): Node
    {
        $this->allowedNodeTypes = $allowedNodeTypes;
        return $this;
    }

    /**
     * Returns the current allowed node types.
     * @return array
     */
    public function getAllowedNodeTypes(): array
    {
        return $this->allowedNodeTypes;
    }

    /**
     * Gets the complete sitemap as an unordered list.
     * @param int $parentId
     * @return string
     */
    protected function renderSitemap(int $parentId): string
    {
        $renderedSitemap = '';

        if ($this->resetable && $parentId == $this->startNode) {
            $renderedSitemap .= sprintf('<li><a data-node="0" href="#">%s</a></li>', $this->resetLabel);
        }

        /** @var \Nzxt\Model\Node $child */
        foreach ($this->retrieveChildrenToRender($parentId) as $child) {
            $renderedSitemap .= $this->renderSingleItem($child);
        }

        return '<ul>' . $renderedSitemap . '</ul>';
    }

    /**
     * Renders a single node item into the sitemap.
     * @param \Nzxt\Model\Node $node
     * @return string
     */
    protected function renderSingleItem(\Nzxt\Model\Node $node): string
    {
        $isSelectable = $this->isItemSelectable($node);
        $renderedItem = sprintf('<i class="%s %s"></i>', $node->getIcon(), $isSelectable ? 'enabled' : 'disabled');

        if ($isSelectable) {
            $renderedItem .= sprintf(
                '<a data-node="%d" data-icon="%s" href="#" title="ID #%d">%s</a>',
                $node->getID(),
                $node->getIcon(),
                $node->getID(),
                htmlspecialchars($node->getTitle())
            );
        } else {
            $renderedItem .= htmlspecialchars($node->getTitle());
        }

        $renderedItem .= $this->renderSitemap($node->getID());

        return '<li>' . $renderedItem . '</li>';
    }

    /**
     * Returns true if the given node is selectable in the sitemap.
     * @param \Nzxt\Model\Node $node
     * @return bool
     */
    protected function isItemSelectable(\Nzxt\Model\Node $node): bool
    {
        $isSelectable = (int) $node->getID() !== (int) $this->getValue();

        if (count($this->allowedNodeTypes)) {
            $isSelectable &= in_array($node->getFieldValue('content_classname'), $this->allowedNodeTypes);
        }

        return $isSelectable;
    }

    /**
     * Retrieves children of a given start node id.
     * @param int $parentId
     * @return array
     */
    protected function retrieveChildrenToRender(int $parentId): array
    {
        return \Nzxt\Model\Node::findByQuery('*', 'pid = ' . $parentId, 'sort')->toArray();
    }
}
