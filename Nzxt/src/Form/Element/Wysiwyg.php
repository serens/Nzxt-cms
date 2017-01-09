<?php
namespace Nzxt\Form\Element;

use Signature\Html\Form\Element\Textarea;

/**
 * Class Wysiwyg
 * @package Nzxt\Form\Element
 */
class Wysiwyg extends Textarea
{
    public function __construct($name, $value = '', array $attributes = [])
    {
        $attributes['data-rte-enabled'] = '1';
        parent::__construct($name, $value, $attributes);
    }
}
