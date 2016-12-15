<?php
namespace Application\Model\Content;

use Nzxt\Model\Content\AbstractContent;

/**
 * Class Service
 * @package Application\Model\Content
 */
class Service extends AbstractContent
{
    protected $title = 'Service';

    protected $canHaveChildNodes = false;

    /**
     * Description of the field elements.
     * @var array
     */
    protected $fieldDescription = [
        'icon' => [
            'elementClassname' => \Signature\Html\Form\Element\Input::class,
            'label' => 'Icon',
        ],
        'description' => [
            'elementClassname' => \Signature\Html\Form\Element\Input::class,
            'label' => 'Description',
        ],
    ];
}
