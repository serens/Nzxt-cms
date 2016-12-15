<?php
namespace Application\Model\Content;

use Nzxt\Model\Content\AbstractContent;
use Signature\Mvc\View\ViewInterface;

/**
 * Class Member
 * @package Application\Model\Content
 */
class Member extends AbstractContent
{
    protected $title = 'Team member';

    protected $canHaveChildNodes = false;

    /**
     * Description of the field elements.
     * @var array
     */
    protected $fieldDescription = [
        'fullname' => [
            'elementClassname' => \Signature\Html\Form\Element\Input::class,
            'label' => 'Fullname',
        ],
        'position' => [
            'elementClassname' => \Signature\Html\Form\Element\Input::class,
            'label' => 'Description',
        ],
        'image' => [
            'elementClassname' => \Nzxt\Form\Element\File::class,
            'label' => 'Image',
        ],
    ];

    /**
     * Assign the image node to the view.
     * @return ViewInterface
     */
    protected function getView(): ViewInterface
    {
        $view = parent::getView();

        return $view->setViewData('imageNode', \Nzxt\Model\Node::find($this->getFieldValue('image')));
    }
}
