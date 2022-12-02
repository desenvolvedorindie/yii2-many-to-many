# Yii 2 Many-to-many

Implementation of [Many-to-many relationship](http://en.wikipedia.org/wiki/Many-to-many_%28data_model%29)
for Yii 2 framework.

**Created by [arogachev](https://github.com/arogachev), forked by [AntonyZ89](https://github.com/AntonyZ89)**

[![Latest Stable Version](https://poser.pugx.org/antonyz89/yii2-many-to-many/v/stable)](https://packagist.org/packages/antonyz89/yii2-many-to-many)
[![Total Downloads](https://poser.pugx.org/antonyz89/yii2-many-to-many/downloads)](https://packagist.org/packages/antonyz89/yii2-many-to-many)
[![Latest Unstable Version](https://poser.pugx.org/antonyz89/yii2-many-to-many/v/unstable)](https://packagist.org/packages/antonyz89/yii2-many-to-many)
[![License](https://poser.pugx.org/arogachev/yii2-many-to-many/license)](https://packagist.org/packages/arogachev/yii2-many-to-many)

- [Yii 2 Many-to-many](#yii-2-many-to-many)
  - [Installation](#installation)
  - [Features](#features)
  - [Creating editable attribute](#creating-editable-attribute)
  - [Attaching and configuring behavior](#attaching-and-configuring-behavior)
  - [Attribute validation](#attribute-validation)
  - [Adding control to view](#adding-control-to-view)

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist antonyz89/yii2-many-to-many
```

or add

```
"antonyz89/yii2-many-to-many": "0.3.*"
```

to the require section of your `composer.json` file.

## Features

- Configuring using existing ```hasMany``` relations
- Multiple relations
- No extra queries. For example, if initially model has 100 related records,
after adding just one, exactly one row will be inserted. If nothing was changed, no queries will be executed.
- Auto filling of editable attribute
- Validator for checking if the received list is valid

## Creating editable attribute

Simply add public property to your `ActiveRecord` model like this:

```php
/** @var int[] */
public $editableRoles = [];
```

It will store primary keys of related records during update.

## Attaching and configuring behavior

First way is to explicitly specify all parameters:

```php
namespace common\models;

use antonyz89\ManyToMany\behaviors\ManyToManyBehavior;

class User extends ActiveRecord {

    /** @var int[] */
    public $editableRoles = [];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => ManyToManyBehavior::class,
                'relations' => [
                    [
                        // Editable attribute name
                        'editableAttribute' => 'editableRoles', 
                        // Model of the junction table
                        'modelClass' => UserRole::class, 
                        // Name of the column in junction table that represents current model
                        'ownAttribute' => 'user_id', 
                        // Related model class
                        'relatedModel' => Role::class,
                        // Name of the column in junction table that represents related model
                        'relatedAttribute' => 'role_id', 
                    ],
                ],
            ],
        ];
    }
}
```

## Attribute validation

Add editable attribute to model rules for massive assignment.

```php
public function rules()
{
    ['editableRoles', 'required'],
    ['editableRoles', 'integer'],
    ['editableRoles', 'each', 'skipOnEmpty' => false, 'rule' => [
        'exist', 'skipOnError' => true, 'targetClass' => Role::class, 'targetAttribute' => ['editableRoles' => 'id']
    ]],
}
```

Or use custom validator:

```php
use antonyz89\ManyToMany\validators\ManyToManyValidator;

public function rules()
{
    ['editableRoles', ManyToManyValidator::class],
}
```

Validator checks list for being array and containing only primary keys presented in related model.
It can not be used without attaching `ManyToManyBehavior`.

## Adding control to view

Add control to view for managing related list. Without extensions it can be done with multiple select:

```php
<?= $form->field($model, 'editableRoles')->dropDownList(Role::getList(), ['multiple' => true]) ?>
```

Example of `getList()` method contents (it needs to be placed in `User` model):

```php
use yii\helpers\ArrayHelper;

/**
 * @return array
 */
public static function getList()
{
    $models = static::find()->orderBy('name')->asArray()->all();

    return ArrayHelper::map($models, 'id', 'name');
}
```
