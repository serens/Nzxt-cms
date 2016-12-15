<?php
namespace Nzxt\Model\Content\Auth;

use Nzxt\Model\Content\AbstractContent;

/**
 * Class User
 * @package Nzxt\Model\Content\Auth
 */
class User extends AbstractContent
{
    protected $icon = 'fa fa-user-o';

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->getFieldValue('username');
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->getFieldValue('password');
    }

    /**
     * @return string
     */
    public function getFullname(): string
    {
        return $this->getNode()->getTitle();
    }
}
