<?php
namespace Application\Model\Content;

use Nzxt\Model\Content\AbstractContent;
use Nzxt\Model\Node;
use Signature\Mvc\View\ViewInterface;

/**
 * Class Visual
 * @package Application\Model\Content
 */
class Visual extends AbstractContent
{
    protected $title = 'Big visual';

    protected $description = 'A big visual on a page.';

    protected $canHaveChildNodes = false;

    /**
     * Description of the field elements.
     * @var array
     */
    protected $fieldDescription = [
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

        return $view->setViewData('imageNode', Node::find($this->getFieldValue('image')));
    }
}
