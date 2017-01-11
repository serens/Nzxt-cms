<?php
namespace Nzxt\Model\Content\Auth;

use Nzxt\Model\Content\AbstractContent;

/**
 * Class Role
 * @package Nzxt\Model\Content\Auth
 */
class Role extends AbstractContent
{
    protected $icon = 'fa fa-user-o';

    protected $title = 'User role';

    /**
     * Description of the field elements.
     * @var array
     */
    protected $fieldDescription = [
        'description' => [
            'elementClassname' => \Signature\Html\Form\Element\Textarea::class,
        ],
        'can_edit' => [
            'elementClassname' => \Signature\Html\Form\Element\Select::class,
            'label' => 'Edit',
        ],
        'can_delete' => [
            'elementClassname' => \Signature\Html\Form\Element\Select::class,
            'label' => 'Delete',
        ],
        'can_add' => [
            'elementClassname' => \Signature\Html\Form\Element\Select::class,
            'label' => 'Add',
        ],
    ];

    /**
     * @return bool
     */
    public function canEdit(): bool
    {
        return $this->hasPrivilege('can_edit');
    }

    /**
     * @return bool
     */
    public function canDelete(): bool
    {
        return $this->hasPrivilege('can_delete');
    }

    /**
     * @return bool
     */
    public function canAdd(): bool
    {
        return $this->hasPrivilege('can_add');
    }

    /**
     * @param string $privilege
     * @return bool
     */
    public function hasPrivilege(string $privilege): bool
    {
        return $this->hasField($privilege) && 1 === (int) $this->getFieldValue($privilege);
    }

    /**
     * Configure select fields.
     * @return array
     */
    public function getScaffoldedFormElements(): array
    {
        $fields  = parent::getScaffoldedFormElements();
        $options = ['0' => 'no', '1' => 'yes'];

        $fields['can_edit']->setOptions($options);
        $fields['can_delete']->setOptions($options);
        $fields['can_add']->setOptions($options);

        return $fields;
    }
}
