<?php

namespace antonyz89\ManyToMany\behaviors;

use antonyz89\ManyToMany\components\ManyToManyRelation;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * @property bool $canAutoFill
 * @property ManyToManyRelation[] $manyToManyRelations
 */
class ManyToManyBehavior extends Behavior
{

    /**
     * Enable auto fill for all ManyToMany relations
     * 
     * @var boolean
     */
    public static $enableAutoFill = true;

    /**
     * Autofill relations on trigger `ActiveRecord::EVENT_AFTER_FIND`
     * 
     * if `false`, you need call `$model->fill()` manually
     * if `null`, the default value will be `static::$enableAutoFill`
     * 
     * @var boolean|null
     */
    public $autoFill = null;

    /**
     * @var array
     */
    public $relations = [];

    /**
     * @var ManyToManyRelation[]
     */
    protected $_relations = [];


    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_INIT => 'customInit',
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
        ];
    }

    public function customInit()
    {
        foreach ($this->relations as $config) {
            $config['model'] = $this->owner;
            $this->_relations[] = new ManyToManyRelation($config);
        }
    }

    public function afterFind()
    {
        if ($this->canAutoFill) {
            $this->fill();
        }
    }

    public function fill()
    {
        foreach ($this->_relations as $relation) {
            $relation->autoFill();
        }
    }

    public function afterInsert()
    {
        foreach ($this->_relations as $relation) {
            $relation->insert();
        }
    }

    public function afterUpdate()
    {
        foreach ($this->_relations as $relation) {
            $relation->update();
        }
    }

    /**
     * @return ManyToManyRelation[]
     */
    public function getManyToManyRelations()
    {
        return $this->_relations;
    }

    /**
     * @param string $name
     * @return ManyToManyRelation|null
     */
    public function getManyToManyRelation($name)
    {
        foreach ($this->_relations as $relation) {
            if ($relation->name == $name || $relation->table == $name) {
                return $relation;
            }
        }

        return null;
    }

    public function getCanAutoFill()
    {
        return $this->autoFill ?? static::$enableAutoFill;
    }
}
