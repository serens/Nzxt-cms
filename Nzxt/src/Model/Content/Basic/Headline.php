<?php
namespace Nzxt\Model\Content\Basic;

use Nzxt\Model\Content\AbstractContent;

/**
 * Class Headline
 * @package Nzxt\Model\Content\Basic
 */
class Headline extends AbstractContent
{
    protected $description = 'A simple headline.';

    protected $canHaveChildNodes = false;

    /**
     * Description of the field elements.
     * @var array
     */
    protected $fieldDescription = [
        'level' => [
            'elementClassname' => \Signature\Html\Form\Element\Select::class,
            'label' => 'Level',
            'description' => 'Indicates the level of the headline.',
        ],
    ];

    /**
     * Fills the form element with data.
     * @return array
     */
    public function getScaffoldedFormElements(): array
    {
        $formElements = parent::getScaffoldedFormElements();

        $select = $formElements['level'];
        $select->setOptions([
            '1' => 'Headline 1',
            '2' => 'Headline 2',
            '3' => 'Headline 3',
            '4' => 'Headline 4',
            '5' => 'Headline 5',
            '6' => 'Headline 6',
        ]);

        return $formElements;
    }
}
