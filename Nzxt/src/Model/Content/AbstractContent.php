<?php
namespace Nzxt\Model\Content;

use Nzxt\Model\AbstractModel;
use Nzxt\Model\Node;
use \Signature\Core\AutoloaderInterface;
use Signature\Mvc\View\PhpView;
use Signature\Mvc\View\ViewInterface;
use Signature\Object\ObjectProviderService;

/**
 * Class AbstractContent
 * @package Nzxt\Model\Content\Type
 */
abstract class AbstractContent extends AbstractModel
{
    /**
     * The description of each field in this content type.
     * @var array
     */
    protected $fieldDescription = [];

    /**
     * @var string
     */
    protected $icon = 'fa fa-cube';

    /**
     * @var string
     */
    protected $title = null;

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var array
     */
    protected $allowedTypesAsChildren = [];

    /**
     * @var bool
     */
    protected $canHaveChildNodes = true;

    /**
     * @var array
     */
    protected $availableSectionNames = [];

    /**
     * @var Node
     */
    protected $node = null;

    /**
     * Returns the node this type belongs to.
     * @return Node|null
     */
    public function getNode()
    {
        if (null === $this->node && $this->hasField('node_id')) {
            /** @var Node $node */
            if ($node = Node::find((int) $this->getFieldValue('node_id'))) {
                $this->node = $node;
            }
        }

        return $this->node;
    }

    /**
     * Assigns the node this type belongs to.
     * @param Node $node
     * @return void
     */
    public function setNode(Node $node)
    {
        $this->node = $node;
    }

    /**
     * Returns further information on a content type.
     *
     * Any content type can override this method to provide specific information.
     * @return array
     */
    public function getInformation(): array
    {
        return [];
    }

    /**
     * Returns an array with information of the fields of this content.
     *
     * Each field describes the field type and a field information text.
     * @return array
     */
    public function getFieldDescription(): array
    {
        // Apply missing information to array
        $fieldDescription = $this->fieldDescription;

        foreach ($fieldDescription as $field => $description) {
            if (empty($description['elementClassname'])) {
                $fieldDescription[$field]['elementClassname'] = \Signature\Html\Form\Element\Input::class;
            }

            if (empty($description['label'])) {
                $fieldDescription[$field]['label'] = ucfirst($field);
            }

            if (empty($description['description'])) {
                $fieldDescription[$field]['description'] = '';
            }
        }

        return $fieldDescription;
    }

    /**
     * Gets the css icon-identifier name of a icon which represents this content.
     *
     * This method should return a css-class from Font Awesome <http://fontawesome.io/cheatsheet/>.
     * When using a Font Awesome css-class, please add the css class 'fa' as well.
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * Returns the description of this content.
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Returns the title of this content.
     *
     * If no title has been defined, the last segment of the class name will be used instead.
     * @return string
     */
    public function getTitle(): string
    {
        if (null === $this->title) {
            $classNameParts = explode('\\', get_class($this));
            $this->title = array_pop($classNameParts);
        }

        return $this->title;
    }

    /**
     * Returns the table name in which the content is stored in.
     * @return string
     */
    public function getTableName(): string
    {
        $classParts = explode('\\', get_class($this));

        return strtolower(implode('_', $classParts));
    }

    /**
     * Returns an array of allowed sub content types.
     *
     * If the array contains no elements, all content types are allowed.
     * @return array
     */
    public function getAllowedSubContentForChildren(): array
    {
        return $this->allowedTypesAsChildren;
    }

    /**
     * Returns a list of available section names in which sub nodes can be created.
     *
     * While creating a new node, the available section names from the new node's parent will be selectable.
     *
     * The default section name is an empty string ("").
     * @return array
     */
    public function getAvailableSectionNames(): array
    {
        return $this->availableSectionNames;
    }

    /**
     * Returns true if this content allowes further more child nodes.
     * @return bool
     */
    public function canHaveChildNodes(): bool
    {
        return $this->canHaveChildNodes;
    }

    /**
     * Returns a set of form fields which allows to edit each field/property of this type.
     * @return array<\Signature\Html\Form\Element\ElementInterface>
     */
    public function getScaffoldedFormElements(): array
    {
        $formElements     = [];
        $fieldDescription = $this->getFieldDescription();

        foreach (array_keys($fieldDescription) as $propertyName) {
            $formElements[$propertyName] = new $fieldDescription[$propertyName]['elementClassname'](
                $propertyName,
                $this->getFieldValue($propertyName)
            );
        }

        return $formElements;
    }

    /**
     * Renders this content using a PhpView.
     *
     * A content class may override this method and return a string.
     *
     * If an empty string is returned the node-renderer will insert a default-content if a user is logged in.
     * @return string
     */
    public function render(): string
    {
        $view = $this->getView();

        return file_exists($view->getTemplate()) ? $view->render() : '';
    }

    /**
     * Returns a view to render the content.
     * @return ViewInterface
     */
    protected function getView(): ViewInterface
    {
        $objectProviderService = ObjectProviderService::getInstance();

        /** @var PhpView $view */
        $view = $objectProviderService->create(PhpView::class);

        return $view
            ->setViewData($this->getFieldValues())
            ->setViewData('node', $this->getNode())
            ->setViewData('imageService', $objectProviderService->getService('ImageService'))
            ->setTemplate($this->resolveViewTemplateName());
    }

    /**
     * Returns a view template name for this content.
     * @return string
     */
    protected function resolveViewTemplateName(): string
    {
        $contentClassName = ltrim(get_class($this), '\\');
        $namespaceParts   = explode('\\', $contentClassName);
        $moduleName       = array_shift($namespaceParts);

        // Get rid of all namespace parts until 'Content' is reached
        while ($namespaceParts[0] != 'Content') {
            array_shift($namespaceParts);
        }

        $templateParts = [
            AutoloaderInterface::MODULES_PATHNAME,
            $moduleName,
            'tpl',
            implode(DIRECTORY_SEPARATOR, $namespaceParts) . '.phtml'
        ];

        return implode(DIRECTORY_SEPARATOR, $templateParts);
    }
}
