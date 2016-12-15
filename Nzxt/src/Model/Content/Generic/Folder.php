<?php
namespace Nzxt\Model\Content\Generic;

use Nzxt\Model\Content\AbstractContent;

/**
 * Class Folder
 * @package Nzxt\Model\Content\Generic
 */
class Folder extends AbstractContent
{
    protected $icon = 'fa fa-folder-o';

    protected $description = 'A folder can have any kind of further more nodes but cannot be rendered. Its purpose is to structure nodes.';

    /**
     * Return a specific icon based on the name of the Folder.
     * @return string
     */
    public function getIcon(): string
    {
        if ($node = $this->getNode()) {
            switch (strtolower($node->getTitle())) {
                case 'system':
                    $this->icon = 'fa fa-gear';
                    break;

                case 'website':
                    $this->icon = 'fa fa-home';
                    break;

                case 'users & roles':
                    $this->icon = 'fa fa-users';
                    break;

                case 'assets':
                case 'files':
                    $this->icon = 'fa fa-picture-o';
                    break;
            }
        }

        return parent::getIcon();
    }
}
