<?php

namespace antonyz89\ManyToMany\components;

use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class ManyToManyRelation extends BaseObject
{
    /**
     * @var string
     */
    public $editableAttribute;

    /**
     * @var ActiveRecord
     */
    public $modelClass;

    /**
     * @var string
     */
    public $ownAttribute;

    /**
     * @var string
     */
    public $relatedModel;

    /**
     * @var string
     */
    public $relatedAttribute;

    /**
     * @var boolean|callable
     */
    public $autoFill = true;

    /**
     * @var \yii\db\ActiveRecord
     */
    protected $_model;

    /**
     * @var array
     */
    protected $_relatedList;

    /**
     * @var boolean
     */
    protected $_filled = false;


    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->editableAttribute) {
            throw new InvalidConfigException('$editableAttribute is required.');
        }

        if (!$this->modelClass) {
            throw new InvalidConfigException('$modelClass is required..');
        }

        if (!$this->ownAttribute) {
            throw new InvalidConfigException('$ownAttribute is required..');
        }

        if (!$this->relatedModel) {
            throw new InvalidConfigException('$relatedModel is required..');
        }

        if (!$this->relatedAttribute) {
            throw new InvalidConfigException('$relatedAttribute is required..');
        }

        parent::init();
    }

    /**
     * @throws \yii\db\Exception
     */
    public function insert()
    {
        $primaryKeys = $this->getAddedPrimaryKeys();
        if (!$primaryKeys) {
            return;
        }

        $transaction = Yii::$app->db->beginTransaction();
        $valid = true;
        try {
            foreach ($primaryKeys as $primaryKey) {
                /** @var ActiveRecord $model */
                $model = new $this->modelClass;
                $model->setAttribute($this->ownAttribute, $this->_model->primaryKey);
                $model->setAttribute($this->relatedAttribute, $primaryKey);

                $valid &= $model->save();
            }
        } catch (\Exception $e) {
            $transaction->rollback();
        }

        if ($valid)
            $transaction->commit();
        else
            $transaction->rollBack();
    }

    public function update()
    {
        if (!$this->_filled) {
            return;
        }

        $this->delete();
        $this->insert();
    }

    /**
     * @throws \yii\db\Exception
     */
    public function delete()
    {
        $primaryKeys = $this->getDeletedPrimaryKeys();
        if (!$primaryKeys) {
            return;
        }

        Yii::$app->db->createCommand()->delete($this->modelClass::tableName(), [
            $this->ownAttribute => $this->_model->primaryKey,
            $this->relatedAttribute => $primaryKeys,
        ])->execute();
    }

    public function autoFill()
    {
        if (is_callable($this->autoFill) && !call_user_func($this->autoFill, $this->_model)) {
            return;
        }

        if (!$this->autoFill) {
            return;
        }

        $this->fill();
    }

    public function fill()
    {
        $this->setEditableList($this->getRelatedList());
        $this->_filled = true;
    }

    /**
     * @return array
     */
    public function getAddedPrimaryKeys()
    {
        return array_values(array_diff($this->getEditableList(), $this->getRelatedList()));
    }

    /**
     * @return array
     */
    public function getDeletedPrimaryKeys()
    {
        return array_values(array_diff($this->getRelatedList(), $this->getEditableList()));
    }

    /**
     * @param \yii\db\ActiveRecord $value
     */
    public function setModel($value)
    {
        $this->_model = $value;
    }

    /**
     * @return array
     */
    protected function getEditableList()
    {
        return $this->_model->{$this->editableAttribute} ?: [];
    }

    /**
     * @param array $value
     */
    protected function setEditableList($value)
    {
        $this->_model->{$this->editableAttribute} = $value;
    }

    /**
     * @return array
     */
    protected function getRelatedList()
    {
        if ($this->_relatedList) {
            return $this->_relatedList;
        }

        $rows = (new Query)
            ->from($this->modelClass::tableName())
            ->select($this->relatedAttribute)
            ->where([$this->ownAttribute => $this->_model->primaryKey])
            ->all();

        $primaryKeys = ArrayHelper::getColumn($rows, $this->relatedAttribute);

        $this->_relatedList = $primaryKeys;

        return $primaryKeys;
    }

    /**
     * @return null|\yii\db\ActiveQuery
     */
    protected function getQuery()
    {
        if (!$this->name) {
            return null;
        }

        $methodName = 'get' . ucfirst($this->name);

        return $this->_model->$methodName();
    }
}
