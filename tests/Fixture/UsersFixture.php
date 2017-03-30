<?php

namespace Robotusers\TableInheritance\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UsersFixture extends TestFixture
{

    /**
     * fields property
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer'],
        'discriminator' => ['type' => 'string'],
        'name' => ['type' => 'string'],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]]
    ];

    public $records = [
        [
            'id' => 1,
            'name' => 'John',
            'discriminator' => 'Authors'
        ],
        [
            'id' => 2,
            'name' => 'Jane',
            'discriminator' => 'Editors'
        ]
    ];
}
