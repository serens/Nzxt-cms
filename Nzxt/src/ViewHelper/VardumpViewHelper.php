<?php
namespace Nzxt\ViewHelper;

use Signature\ViewHelper\ArgumentDescription;

/**
 * Class VardumpViewHelper
 * @package Nzxt\ViewHelper
 */
class VardumpViewHelper extends \Signature\ViewHelper\AbstractViewHelper
{
    /**
     * Creates the argument descriptions for this view helper.
     */
    public function __construct()
    {
        $this->argumentDescriptions = [
            'var' => new ArgumentDescription(true, 'mixed'),
        ];

        parent::__construct();
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $var = $this->getArgument('var');

        switch (gettype($var)) {
            case 'NULL':
                return 'NULL';
            case 'string':
                return 'STRING(' . ($var ? ('"' . $var . '"'): '<i>empty</i>') . ')';
            case 'boolean':
                return 'BOOL(' . ($var ? 'true' : 'false') . ')';
            case 'integer':
                return 'INT(' . $var . ')';
            case 'double':
                return 'DOUBLE(' . $var . ')';
            case 'array':
                return 'ARRAY[' . implode(', ', $var) . ']';
        }
    }
}
