# Yii 2 Many-to-many

Implementation of [Many-to-many relationship](http://en.wikipedia.org/wiki/Many-to-many_%28data_model%29)
for Yii 2 framework.

[![Latest Stable Version](https://poser.pugx.org/arogachev/yii2-many-to-many/v/stable)](https://packagist.org/packages/arogachev/yii2-many-to-many)
[![Total Downloads](https://poser.pugx.org/arogachev/yii2-many-to-many/downloads)](https://packagist.org/packages/arogachev/yii2-many-to-many)
[![Latest Unstable Version](https://poser.pugx.org/arogachev/yii2-many-to-many/v/unstable)](https://packagist.org/packages/arogachev/yii2-many-to-many)
[![License](https://poser.pugx.org/arogachev/yii2-many-to-many/license)](https://packagist.org/packages/arogachev/yii2-many-to-many)

- [Installation](#installation)
- [Features](#features)
- [Creating editable attribute](#creating-editable-attribute)
- [Attaching and configuring behavior](#attaching-and-configuring-behavior)
- [Filling relations](#filling-relations)
- [Saving relations without massive assignment](#saving-relations-without-massive-assignment)
- [Adding attribute as safe](#adding-attribute-as-safe)
- [Adding control to view](#adding-control-to-view)
- [Relation features](#relation-features)
- [Running tests](#running-tests)

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
/**
 * @var array
 */
public $editableUsers = [];
```

It will store primary keys of related records during update.

## Attaching and configuring behavior

First way is to explicitly specify all parameters:

```php
use antonyz89\ManyToMany\behaviors\ManyToManyBehavior;

/**
 * @inheritdoc
 */
public function behaviors()
{
    return [
        [
            'class' => ManyToManyBehavior::className(),
            'relations' => [
                [
                    'editableAttribute' => 'editableUsers', // Editable attribute name
                    'table' => 'tests_to_users', // Name of the junction table
                    'ownAttribute' => 'test_id', // Name of the column in junction table that represents current model
                    'relatedModel' => User::className(), // Related model class
                    'relatedAttribute' => 'user_id', // Name of the column in junction table that represents related model
                ],
            ],
        ],
    ];
}
```

## Adding attribute as safe

Add editable attribute to model rules for massive assignment.

Either mark it as safe at least:

```php
public function rules()
{
    ['editableUsers', 'safe'],
}
```

Or use custom validator:

```php
use antonyz89\ManyToMany\validators\ManyToManyValidator;

public function rules()
{
    ['editableUsers', ManyToManyValidator::className()],
}
```

Validator checks list for being array and containing only primary keys presented in related model.
It can not be used without attaching `ManyToManyBehavior`.
