# CakePHP Table Inheritance plugin

This plugin implements [Single Table Inheritance](https://en.wikipedia.org/wiki/Single_Table_Inheritance) (and hopefully will implement Class Table Inheritance in the future) patterns for CakePHP 3.x ORM.

**NOTE: This plugin is under development! Use it at your own risk**

## Installation

Using composer:
```
composer require robotusers/cakephp-table-inheritance ~0.2
```

## How to make this work?

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

`StiBehavior` supports following options:

* `discriminatorField` - db table field used to discriminate models, 'discriminator' by default
* `discriminator` - discriminator value, `$table->alias()` by default
* `table` - db table to share, use this option or `$table->table()` method.
* `checkRules` - `true` by default. Allows to enable/disable build-in rule check for a discriminator value.

## StiParentBehavior

This plugin also allows to configure parent Table in order to create and hydrate entities based on child tables.

```php
//in UsersTable:
public function initialize(array $config)
{
    $this->addBehavior('Robotusers/TableInheritance.StiParent', [
        'tableMap' => [
            'admin' => 'Administrators',
            'client' => 'Clients'
        ]
    ]);
}
```

`tableMap` option accepts an array mapping discriminator field values to child tables. It accepts both `Table` objects and registry aliases.

This behavior also provides `newStiEntity()` method which will proxy `newEntity()` to one of the configured tables based on a discriminator value.

```php
$data = [
    'name' => 'super-admin',
    'discriminator' => 'admin'
];

$admin = $this->Users->newStiEntity($data); //will call AdministratorsTable::newEntity() and return an Administrator entity instance.
```
