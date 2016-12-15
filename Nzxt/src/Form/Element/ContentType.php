<?php
namespace Nzxt\Form\Element;

use Nzxt\Model\Content\AbstractContent;
use Nzxt\Model\Node;
use Nzxt\Utility\Loader\ContentLoader;
use Signature\Html\Form\Element\Hidden;
use Signature\Object\ObjectProviderService;

/**
 * Class ContentSelection
 * @package Nzxt\Form\Element
 */
class ContentType extends Hidden
{
    /**
     * @var array
     */
    protected $contentTypes = [];

    /**
     * @var string
     */
    protected $noContentTypeSelectedLabel = '(No content type)';

    /**
     * @var ObjectProviderService
     */
    protected $objectProviderService = null;

    /**
     * @param string $name
     * @param string $value
     * @param array $attributes
     * @param Node $node
     */
    public function __construct(string $name, string $value = '', array $attributes = [], Node $node = null)
    {
        $this->objectProviderService = ObjectProviderService::getInstance();

        foreach (ContentLoader::findContentClasses() as $module => $classNames) {
            foreach ($classNames as $className) {
                if (!array_key_exists($module, $this->contentTypes)) {
                    $this->contentTypes[$module] = [];
                }

                // If node is given, check on allowed content types
                if (null !== $node && ($content = $node->getContent())) {
                    $allowedClassnames = $content->getAllowedSubContentForChildren();

                    if (count($allowedClassnames) && !in_array($className, $allowedClassnames)) {
                        continue;
                    }
                }

                try {
                    /** @var AbstractContent $contentType */
                    $contentType = $this->objectProviderService->create($className);

                    // use the module name of each element to categorize the list
                    $this->contentTypes[$module][] = $contentType;
                } catch (\Exception $e) {

                }
            }
        }

        parent::__construct($name, $value, $attributes);
    }

    /**
     * Renders the input.
     * @return string
     */
    public function render(): string
    {
        $contentType = $this->getValue() ? $this->objectProviderService->create($this->getValue()) : null;
        $value = $contentType
            ? sprintf('<i class="%s fa-fw"></i> %s', $contentType->getIcon(), $contentType->getTitle())
            : '&nbsp;';

        $elementID = 'contenttype-select-input-' . $this->getAttribute('id');
        $userInput = '
            <div class="user-input contenttype-select-input" id="' . $elementID . '">' .
                parent::render() . '
                <div class="value">' . $value . '</div>
                <a class="panel-trigger" title="Click to select a content type." href="#"><i class="fa fa-ellipsis-h"></i></a>
                <div class="panel">' . $this->renderListItems() . '</div>
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

                        if ("" == value) {
                            $valueDisplay.text("' . $this->noContentTypeSelectedLabel . '");
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

                    // Disable scrolling the parent window, when hovering over input
                    $container
                        .bind("mouseenter", function() {
                            if ($container.hasClass(panelOpenedClassname)) {
                                DialogController.setEnableBodyScroll(false);
                            }
                        })
                        .bind("mouseleave", function() {
                            DialogController.setEnableBodyScroll(true);
                        });

                    $("a", $panel).click(function(e) {
                        var $link = $(this);

                        e.preventDefault();

                        $panel.hide();
                        $container.removeClass(panelOpenedClassname);
                        $valueDisplay.html("<i class=\"" + $link.data("icon") + " fa-fw\"></i> " + $link.text());
                        $input.val($link.data("value")).trigger("update");
                    });
                });
            </script>
        ';

        return $userInput . $javaScript;
    }

    /**
     * Renders a single option of the set of options.
     * @throws \OutOfRangeException
     * @return string
     */
    protected function renderListItems(): string
    {
        $markup = $this->renderSingleListItem(); // The 1st list item represents value=""

        foreach ($this->contentTypes as $module => $contentTypes) {
            $markup .= '<li><b>' . $module . ':</b><ul>';

            /** @var AbstractContent $contentType */
            foreach ($contentTypes as $contentType) {
                $markup .= $this->renderSingleListItem($contentType);
            }

            $markup .= '</ul></li>';
        }

        return '<ul>' . $markup . '</ul>';
    }

    /**
     * Renders a single content type out of a list of content types.
     * @param AbstractContent $contentType
     * @return string
     */
    protected function renderSingleListItem(AbstractContent $contentType = null): string
    {
        if (null === $contentType) {
            $listItem = sprintf('<a href="#" data-value="">%s</a>', $this->noContentTypeSelectedLabel);
        } else {
            $listItem = sprintf(
                '%s<a href="#" data-value="%s" data-icon="%s">%s</a>',
                '<i class="' . $contentType->getIcon() . ' fa-fw enabled"></i> ',
                get_class($contentType),
                $contentType->getIcon(),
                $contentType->getTitle()
            );
        }

        return '<li>' . $listItem . '</li>';
    }
}
