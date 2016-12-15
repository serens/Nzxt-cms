<?php
namespace Nzxt\Model;

use Signature\Object\ObjectProviderService;

/**
 * Class AbstractModel
 * @package Nzxt\Model
 */
abstract class AbstractModel extends \Signature\Persistence\ActiveRecord\AbstractRecord
{
    /**
     * @var string
     */
    protected $primaryKeyName = 'id';

    /**
     * Modifies the model by updating the modified-field.
     * @return AbstractModel
     */
    public function touch(): AbstractModel
    {
        if ($this->hasField('modified')) {
            $this->setFieldValue('modified', date('Y-m-d H:i:s'));
        }

        return $this;
    }
}
