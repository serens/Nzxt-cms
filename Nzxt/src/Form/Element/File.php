<?php
namespace Nzxt\Form\Element;

/**
 * Class File
 * @package Nzxt\Form\Element
 */
class File extends Node
{
    /**
     * @var string
     */
    protected $noNodeSelectedLabel = '(No file selected)';

    /**
     * @var array
     */
    protected $allowedNodeTypes = [\Nzxt\Model\Content\Generic\File::class];
}
