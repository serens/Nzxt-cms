<?php
namespace Nzxt\Exception\Node;

/**
 * Class NotFoundException
 * @package Nzxt\Exception\Node
 */
class NodeNotFoundException extends \Exception
{
    /**
     * @param int $nodeId
     */
    public function __construct(int $nodeId)
    {
        $this->message = 'Node with id ' . $nodeId . ' not found.';
    }
}
