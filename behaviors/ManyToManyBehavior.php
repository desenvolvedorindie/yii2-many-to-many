<?php

namespace antonyz89\ManyToMany\behaviors;

use antonyz89\ManyToMany\components\ManyToManyRelation;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class ManyToManyBehavior extends Behavior
{

    public $autoFill = true;
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
        if ($this->autoFill) {
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
}
