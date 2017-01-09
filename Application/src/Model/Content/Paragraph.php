<?php
namespace Application\Model\Content;

use Nzxt\Model\Content\AbstractContent;

/**
 * Class Paragraph
 * @package Application\Model\Content
 */
class Paragraph extends AbstractContent
{
    protected $canHaveChildNodes = false;

    /**
     * Description of the field elements.
     * @var array
     */
    protected $fieldDescription = [
        'style' => [
            'elementClassname' => \Signature\Html\Form\Element\Select::class,
            'label' => 'Style',
        ],
        'content' => [
            'elementClassname' => \Nzxt\Form\Element\Wysiwyg::class,
            'label' => 'Content',
        ],
    ];

    /**
     * Fills the form element with data.
     * @return array
     */
    public function getScaffoldedFormElements(): array
    {
        $formElements = parent::getScaffoldedFormElements();

        $select = $formElements['style'];
        $select->setOptions([
            '' => 'Normal',
            'lead' => 'Lead',
        ]);

        return $formElements;
    }
}
