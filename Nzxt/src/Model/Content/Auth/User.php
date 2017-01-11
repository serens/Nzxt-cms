<?php
namespace Nzxt\Model\Content\Auth;

use Nzxt\Model\Content\AbstractContent;
use Signature\Persistence\ActiveRecord\RecordInterface;

/**
 * Class User
 * @package Nzxt\Model\Content\Auth
 */
class User extends AbstractContent
{
    protected $icon = 'fa fa-user-o';

    /**
     * Description of the field elements.
     * @var array
     */
    protected $fieldDescription = [
        'username' => [
            'elementClassname' => \Signature\Html\Form\Element\Input::class,
        ],
        'password' => [
            'elementClassname' => \Signature\Html\Form\Element\Password::class,
        ],
        'role' => [
            'elementClassname' => \Signature\Html\Form\Element\Select::class,
        ],
    ];

    /**
     * @return string
     */
    public function getFullname(): string
    {
        return $this->getNode()->getTitle();
    }

    /**
     * When the password has changed since it was loaded from the db, it has to be hashed again.
     * @param string $field
     * @param mixed $value
     * @return RecordInterface
     */
    public function setFieldValue(string $field, $value): RecordInterface
    {
        if ('password' == $field && $this->hasField('password') && $value !== $this->getFieldValue('password')) {
            $value = password_hash($value, PASSWORD_DEFAULT);
        }

        return parent::setFieldValue($field, $value);
    }

    /**
     * Gets the role this user is assigned to.
     * @return Role
     */
    public function getRole(): Role
    {
        return Role::find($this->getFieldValue('role'));
    }

    /**
     * Insert all user roles to the user-role-select field.
     * @return array
     */
    public function getScaffoldedFormElements(): array
    {
        $options = [];
        $roles   = Role::findAll();
        $fields  = parent::getScaffoldedFormElements();
        $userRoleSelect = $fields['role'];

        foreach ($roles as $role) {
            $options[$role->getID()] = $role->getNode()->getTitle();
        }

        $userRoleSelect->setOptions($options);

        return $fields;
    }
}
