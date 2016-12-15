<?php
namespace Application\Model\Content;

use Nzxt\Model\Content\AbstractContent;

/**
 * Class Section
 * @package Application\Model\Content
 */
class Section extends AbstractContent
{
    protected $description = 'A section on the page. A section my have more sub nodes such as rows, members or services';

    /**
     * Description of the field elements.
     * @var array
     */
    protected $fieldDescription = [
        'headline' => [
            'elementClassname' => \Signature\Html\Form\Element\Input::class,
            'label' => 'Headline',
        ],
        'introduction' => [
            'elementClassname' => \Signature\Html\Form\Element\Textarea::class,
            'label' => 'Introduction',
        ],
        'type' => [
            'elementClassname' => \Signature\Html\Form\Element\Select::class,
            'label' => 'Type',
        ]
    ];

    /**
     * Sets the options for the type-select field.
     * @return array
     */
    public function getScaffoldedFormElements(): array
    {
        $formElements = parent::getScaffoldedFormElements();

        $formElements['type']->setOptions([
            'normal' => 'Normal',
            'members' => 'Members',
            'services' => 'Services',
        ]);

        return $formElements;
    }

    /**
     * Sets the allowed content types based on the 'type'-property.
     * @return array
     */
    public function getAllowedSubContentForChildren(): array
    {
        switch ($this->getFieldValue('type')) {
            default:
            case 'normal':
                return []; // Allow all content types

            case 'members':
                return [\Application\Model\Content\Member::class];

            case 'services':
                return [\Application\Model\Content\Service::class];
        }
    }
}
