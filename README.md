# CakePHP Table Inheritance plugin

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[![Build Status](https://travis-ci.org/robotusers/cakephp-table-inheritance.svg?branch=master)](https://travis-ci.org/robotusers/cakephp-table-inheritance)

This plugin implements [Single Table Inheritance](https://en.wikipedia.org/wiki/Single_Table_Inheritance) (and hopefully will implement Class Table Inheritance in the future) patterns for CakePHP 3.x ORM.

**NOTE: This plugin is under development! Use it at your own risk**

## Installation

Using composer:
```
composer require robotusers/cakephp-table-inheritance ~0.2
```

## StiBehavior

For now only STI is supported. Just add a behavior to your tables:
```php
//in ClientsTable:
public function initialize(array $config)
{
    $this->addBehavior('Robotusers/TableInheritance.Sti', [
        'table' => 'users',
        'discriminator' => 'client'
    ]);
}

//alternative config in AdministratorsTable:
public function initialize(array $config)
{
    $this->table('users');
    $this->addBehavior('Robotusers/TableInheritance.Sti');
    $this->discriminator('admin');
}
```
Now both the `ClientsTable` and `AdministratorsTable` will share `users` db table. A table has to have a `discriminator` field which will be used to determine which model's record is stored in a row.

### Multiple discriminators ###

You can also configure a list of allowed discriminators. It's useful for example when working with the files.
For example:

```php
//in ImagesTable:
public function initialize(array $config)
{
    $this->addBehavior('Robotusers/TableInheritance.Sti', [
        'table' => 'files',
        'discriminatorField' => 'mime',
        'allowedDiscriminators' => [
            'image/jpeg',
            'image/gif',
            'image/png',
            'image/tiff'
        ]
    ]);
}

//or using wildcards:

public function initialize(array $config)
{
    $this->addBehavior('Robotusers/TableInheritance.Sti', [
        'table' => 'files',
        'discriminatorField' => 'mime',
        'allowedDiscriminators' => [
            'image/*'
        ]
    ]);
}
```

An `ImagesTable` will share `files` db table and match only specified mime types.

### Configuration ###

`StiBehavior` supports following options:

* `discriminatorField` - db table field used to discriminate models, 'discriminator' by default
* `discriminator` - default discriminator value, `$table->alias()` by default
* `table` - db table to share, use this option or `$table->table()` method.
* `checkRules` - `true` by default. Allows to enable/disable build-in rule check for a discriminator value.
* `allowedDiscriminators` - a list of allowed discriminators.

## StiParentBehavior

This plugin also allows to configure parent Table in order to create and hydrate entities based on child tables.

```php
//in UsersTable:
public function initialize(array $config)
{
    $this->addBehavior('Robotusers/TableInheritance.StiParent', [
        'tableMap' => [
            'Administrators' => [
                'admin',
                'administrator'
            ],
            'Clients' => 'client'
        ]
    ]);
}
```

`tableMap` option accepts an array mapping table registry aliases to discriminator field values.

You can also map discriminator values to specified table objects using `discriminatorMap` option:

```php
//in UsersTable:
public function initialize(array $config)
{
    $this->addBehavior('Robotusers/TableInheritance.StiParent', [
        'discriminatorMap' => [
            'admin' => $this->tableLocator()->get('Administrators'),
            'client' => $this->tableLocator()->get('Clients')
        ]
    ]);
}
```

This behavior also provides `newStiEntity()` method which will proxy `newEntity()` to one of the configured tables based on a discriminator value.

```php
$data = [
    'name' => 'super-admin',
    'discriminator' => 'admin'
];

$admin = $this->Users->newStiEntity($data); //will call AdministratorsTable::newEntity() and return an Administrator entity instance.
```

Afterwards you can get a STI table using `stiTable()` method and handle entity using its source `Table` object.

```php
$table = $this->Users->stiTable($admin); 
$table->save($admin); //it will save an entity using AdministratorsTable
```

You can also directly detect STI table from data array:

```php
$data = [
    'name' => 'super-admin',
    'discriminator' => 'admin'
];

$table = $this->Users->stiTable($data);
$admin = $table->newEntity($data);
$table->save($admin);
```
