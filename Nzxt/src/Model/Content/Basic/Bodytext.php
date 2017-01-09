<?php
namespace Nzxt\Model\Content\Basic;

use Nzxt\Model\Content\AbstractContent;

/**
 * Class Bodytext
 * @package Nzxt\Model\Content\Basic
 */
class Bodytext extends AbstractContent
{
    protected $description = 'A text block.';

    protected $canHaveChildNodes = false;

    /**
     * Description of the field elements.
     * @var array
     */
    protected $fieldDescription = [
        'content' => [
            'elementClassname' => \Nzxt\Form\Element\Wysiwyg::class,
            'label' => 'Content',
        ],
    ];
}
