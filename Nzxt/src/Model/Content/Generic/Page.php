<?php
namespace Nzxt\Model\Content\Generic;

use Nzxt\Model\Content\AbstractContent;
use Signature\Mvc\Exception\Exception;

/**
 * Class Page
 * @package Nzxt\Model\Content\Generic
 */
class Page extends AbstractContent
{
    protected $icon = 'fa fa-file-o';

    /**
     * Description of the field elements.
     * @var array
     */
    protected $fieldDescription = [
        'template' => [
            'elementClassname' => \Signature\Html\Form\Element\Select::class,
            'label' => 'Template',
        ],
        'nav_title' => [
            'elementClassname' => \Signature\Html\Form\Element\Input::class,
            'label' => 'Navigation title',
        ],
        'meta_keywords' => [
            'elementClassname' => \Signature\Html\Form\Element\Textarea::class,
            'label' => 'Keywords',
        ],
        'meta_description' => [
            'elementClassname' => \Signature\Html\Form\Element\Textarea::class,
            'label' => 'Description',
        ],
        'meta_author' => [
            'elementClassname' => \Signature\Html\Form\Element\Input::class,
            'label' => 'Author',
        ],
        'meta_author_email' => [
            'elementClassname' => \Signature\Html\Form\Element\Input::class,
            'label' => 'Author email',
        ],
        'meta_copyright' => [
            'elementClassname' => \Signature\Html\Form\Element\Input::class,
            'label' => 'Copyright',
        ],
    ];

    /**
     * @var array
     * @todo Only for testing! Remove this as section definitions belong into Application module!
     */
    protected $availableSectionNames = ['pages', 'header', 'main', 'left', 'right', 'footer'];

    /**
     * Fills the options for the template-select field.
     * @return array
     */
    public function getScaffoldedFormElements(): array
    {
        $formElements = parent::getScaffoldedFormElements();

        $select = $formElements['template'];
        $select->setOptions(\Nzxt\Utility\Loader\TemplateLoader::findTemplates());

        return $formElements;
    }

    /**
     * Returns the content of a page by rendering each sub node excluding pages.
     *
     * The rendered content of sub nodes is passed to an optional template. If no template is specified, only the
     * rendered content of sub nodes will be returned.
     * @return string
     * @throws Exception
     */
    public function render(): string
    {
        $renderedChildrenContent = '';

        /** @var \Nzxt\Model\Node $child */
        foreach ($this->getNode()->getChildren() as $child) {
            // Do not render sub nodes of type 'Page'
            if (($content = $child->getContent()) && get_class($content) == get_class($this)) {
                continue;
            }

            $renderedChildrenContent .= $this->getNode()->getRenderer()->render($child);
        }

        if ($template = $this->getFieldValue('template')) {
            if (!file_exists($template)) {
                throw new Exception(
                    'Assigned template "' . $template . '" cannot be loaded.'
                );
            }

            // Assign new template and special content to view
            return $this->getView()
                ->setViewData('content', $renderedChildrenContent)
                ->setTemplate($template)
                ->render();
        } else {
            return $renderedChildrenContent;
        }
    }
}
