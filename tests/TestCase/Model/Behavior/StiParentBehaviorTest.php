<?php

namespace Robotusers\TableInheritance\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Robotusers\TableInheritance\Test\Mock\Author;
use Robotusers\TableInheritance\Test\Mock\Editor;
use Robotusers\TableInheritance\Test\Mock\User;

/**
 * @author Robert Pustułka robert.pustulka@gmail.com
 * @copyright 2015 RobotUsers
 * @license MIT
 */
class StiParentBehaviorTest extends TestCase
{
    public $fixtures = [
        'plugin.Robotusers\TableInheritance.users'
    ];

    /**
     *
     * @var \Cake\ORM\Table
     */
    public $table;

    public function setUp()
    {
        parent::setUp();

        $this->entityMap = [
            'Authors' => Author::class,
            'Users' => User::class,
            'Editors' => Editor::class,
            '' => User::class
        ];
        
        $this->table = TableRegistry::get('Users');
        $this->table->entityClass(User::class);

        $authors = TableRegistry::get('Authors', [
            'table' => 'users'
        ]);
        $editors = TableRegistry::get('Editors', [
            'table' => 'users'
        ]);

        $authors->addBehavior('Robotusers/TableInheritance.Sti');
        $editors->addBehavior('Robotusers/TableInheritance.Sti');
        $this->table->addBehavior('Robotusers/TableInheritance.StiParent', [
            'tableMap' => [
                'Authors' => 'Authors',
                'Editors' => 'Editors'
            ]
        ]);

        $authors->entityClass(Author::class);
        $editors->entityClass(Editor::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        TableRegistry::clear();
    }

    public function testNewStiEntity()
    {
        $entities = [];
        foreach ($this->entityMap as $discriminator => $class) {
            $data = [
                'discriminator' => $discriminator
            ];

            $entity = $this->table->newStiEntity($data);
            $this->assertInstanceOf($class, $entity);

            $entities[] = $entity;
        }
    }

    public function testFind()
    {
        $entities = [];
        foreach ($this->entityMap as $discriminator => $class) {
            $data = [
                'discriminator' => $discriminator
            ];
            $entities[] = $this->table->newEntity($data);
        }

        $this->table->saveMany($entities);

        $found = $this->table->find()->toArray();
        $this->assertCount(4, $found);

        foreach ($found as $entity) {
            $class = $this->entityMap[$entity->discriminator];
            $this->assertInstanceOf($class, $entity);
        }
    }
}