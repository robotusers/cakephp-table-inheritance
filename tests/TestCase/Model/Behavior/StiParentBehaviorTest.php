<?php

namespace Robotusers\TableInheritance\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Robotusers\TableInheritance\Test\Mock\Author;
use Robotusers\TableInheritance\Test\Mock\Editor;
use Robotusers\TableInheritance\Test\Mock\Reader;
use Robotusers\TableInheritance\Test\Mock\User;

/**
 * @author Robert Pustułka robert.pustulka@gmail.com
 * @copyright 2015 RobotUsers
 * @license MIT
 */
class StiParentBehaviorTest extends TestCase
{
    public $fixtures = [
        'plugin.Robotusers\TableInheritance.Users'
    ];

    /**
     *
     * @var \Cake\ORM\Table
     */
    public $table;

    public function setUp(): void
    {
        parent::setUp();

        $this->entityMap = [
            'Authors' => Author::class,
            'Users' => User::class,
            'Editors' => Editor::class,
            'Readers' => Reader::class,
            'Subscribers' => Reader::class,
            '' => User::class
        ];

        $this->table = TableRegistry::getTableLocator()->get('Users');
        $this->table->setEntityClass(User::class);

        $authors = TableRegistry::getTableLocator()->get('Authors', [
            'table' => 'users'
        ]);
        $editors = TableRegistry::getTableLocator()->get('Editors', [
            'table' => 'users'
        ]);
        $readers = TableRegistry::getTableLocator()->get('Readers', [
            'table' => 'users'
        ]);

        $authors->addBehavior('Robotusers/TableInheritance.Sti');
        $editors->addBehavior('Robotusers/TableInheritance.Sti');
        $readers->addBehavior('Robotusers/TableInheritance.Sti');
        $this->table->addBehavior('Robotusers/TableInheritance.StiParent', [
            'discriminatorMap' => [
                'Authors' => 'Authors',
                'Editors' => 'Editors'
            ],
            'tableMap' => [
                'Readers' => [
                    'Readers',
                    'Subscribers'
                ]
            ]
        ]);

        $authors->setEntityClass(Author::class);
        $editors->setEntityClass(Editor::class);
        $readers->setEntityClass(Reader::class);
    }

    public function testStiTable()
    {
        $this->table->behaviors()->get('StiParent')->setConfig('tableMap', [
            'Readers' => 'reader_*'
        ], false);
        $this->table->behaviors()->get('StiParent')->setConfig('discriminatorMap', [
            '*author' => TableRegistry::getTableLocator()->get('Authors')
        ], false);

        $map = [
            'reader_1' => 'Readers',
            'reader_2' => 'Readers',
            'super_author' => 'Authors',
            'bestselling-author' => 'Authors',
            'other' => 'Users',
            '' => 'Users',
        ];

        foreach ($map as $discriminator => $alias) {
            $entity = $this->table->newEntity([
                'discriminator' => $discriminator
            ]);
            $table = $this->table->stiTable($entity);
            $this->assertEquals($alias, $table->getAlias());
        }
    }

    public function tearDown(): void
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
        $this->assertCount(8, $found);

        foreach ($found as $entity) {
            $class = $this->entityMap[$entity->discriminator];
            $this->assertInstanceOf($class, $entity);
        }
    }
}
