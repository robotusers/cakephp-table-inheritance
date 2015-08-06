# CakePHP Table Inheritance plugin

This plugin implements [Single Table Inheritance](https://en.wikipedia.org/wiki/Single_Table_Inheritance) (and hopefully will implement Class Table Inheritance in the future) patterns for CakePHP 3.x ORM.

# How to make this work?

For now only STI is supported. Just add a behavior to your tables:
```php
//in ClientsTable:
public function initialize(array $config)
{
    $this->addBehavior('Robotusers/TableInheritance.Sti', [
        'table' => 'users'
    ]);
}

//in AdministratorsTable:
public function initialize(array $config)
{
    $this->addBehavior('Robotusers/TableInheritance.Sti', [
        'table' => 'users'
    ]);
}
```
Now both the `ClientsTable` and `AdministratorsTable` will share `users` db table. A table has to have a `discriminator` field which will be used to determine which model's record is stored in a row.

`StiBehavior` supports following options:

* `discriminatorField` - db table field used to discriminate models, 'discriminator' by default
* `discriminator` - discriminator value, `$table->alias()` by default
* `table` - db table to use
