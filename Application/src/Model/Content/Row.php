<?php
namespace Application\Model\Content;

use Nzxt\Model\Content\AbstractContent;

/**
 * Class Row
 * @package Application\Model\Content
 */
class Row extends AbstractContent
{
    protected $title = 'Grid row';

    protected $description = 'A row on a page, typically inside a Section.';

    /**
     * Description of the field elements.
     * @var array
     */
    protected $fieldDescription = [
        'column_definition' => [
            'elementClassname' => \Signature\Html\Form\Element\Select::class,
            'label' => 'Configuration',
        ],
    ];

    /**
     * Fills the form element with data.
     * @return array
     */
    public function getScaffoldedFormElements(): array
    {
        $formElements = parent::getScaffoldedFormElements();

        $select = $formElements['column_definition'];
        $select->setOptions([
            '12' => '12',
            '6 / 6' => '6 / 6',
            '8 / 4' => '8 / 4',
            '4 / 8' => '4 / 8',
            '4 / 4 / 4' => '4 / 4 / 4',
        ]);

        return $formElements;
    }

    /**
     * @return array
     */
    public function getAvailableSectionNames(): array
    {
        switch ($this->getFieldValue('column_definition')) {
            case '12':
                return ['column1'];

            case '6 / 6':
            case '8 / 4':
            case '4 / 8':
                return ['column1', 'column2'];

            case '4 / 4 / 4':
                return ['column1', 'column2', 'column3'];

            default:
                return parent::getAvailableSectionNames();
        }
    }
}
