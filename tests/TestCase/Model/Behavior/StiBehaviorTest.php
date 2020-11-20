<?php

namespace Robotusers\TableInheritance\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * @author Robert Pustułka robert.pustulka@gmail.com
 * @copyright 2017 RobotUsers
 * @license MIT
 */
class StiBehaviorTest extends TestCase
{
    public $fixtures = [
        'plugin.Robotusers\TableInheritance.Users'
    ];

    public function tearDown(): void
    {
        parent::tearDown();

        TableRegistry::clear();
    }

    public function testDiscriminator()
    {
        $table = TableRegistry::get('Authors', [
            'table' => 'users'
        ]);
        $table->addBehavior('Robotusers/TableInheritance.Sti');

        $this->assertEquals('Authors', $table->discriminator());
        $this->assertEquals('author', $table->discriminator('author'));

        $table = TableRegistry::get('Editors', [
            'table' => 'users'
        ]);
        $table->addBehavior('Robotusers/TableInheritance.Sti', [
            'discriminator' => 'editor'
        ]);

        $this->assertEquals('editor', $table->discriminator());
    }

    public function testAcceptedDiscriminators()
    {
        $table = TableRegistry::get('Authors', [
            'table' => 'users'
        ]);
        $table->addBehavior('Robotusers/TableInheritance.Sti');

        $accepted = $table->acceptedDiscriminators();
        $this->assertContains('Authors', $accepted);

        $this->assertTrue($table->isAcceptedDiscriminator('Authors'));
        $this->assertFalse($table->isAcceptedDiscriminator('Editors'));

        $table->addAcceptedDiscriminator('author_*');
        $this->assertTrue($table->isAcceptedDiscriminator('author_foo'));
        $this->assertTrue($table->isAcceptedDiscriminator('author_bar'));
        $this->assertFalse($table->isAcceptedDiscriminator('editor'));
    }

    public function testSave()
    {
        $table = TableRegistry::get('Authors', [
            'table' => 'users'
        ]);
        $table->addBehavior('Robotusers/TableInheritance.Sti');

        $entity = $table->newEntity([
            'name' => 'Robert'
        ]);
        $table->save($entity);

        $this->assertEmpty($entity->getErrors());
        $this->assertEquals('Authors', $entity->discriminator);

        $entity2 = $table->newEntity([
            'name' => 'Robert',
            'discriminator' => 'Editors'
        ]);
        $table->save($entity2);

        $this->assertArrayHasKey('discriminator', $entity2->getErrors());
        $this->assertEquals('Editors', $entity2->discriminator);
    }

    public function testSaveNoRules()
    {
        $table = TableRegistry::get('Authors', [
            'table' => 'users'
        ]);
        $table->addBehavior('Robotusers/TableInheritance.Sti', [
            'checkRules' => false
        ]);

        $entity = $table->newEntity([
            'name' => 'Robert',
            'discriminator' => 'Editors'
        ]);
        $table->save($entity);

        $this->assertEmpty($entity->getErrors());
        $this->assertEquals('Editors', $entity->discriminator);
    }

    public function testSaveWildcard()
    {
        $table = TableRegistry::get('Authors', [
            'table' => 'users'
        ]);
        $table->addBehavior('Robotusers/TableInheritance.Sti', [
            'acceptedDiscriminators' => 'author_*'
        ]);

        $entity = $table->newEntity([
            'name' => 'Robert',
            'discriminator' => 'author_foo'
        ]);
        $table->save($entity);

        $this->assertEmpty($entity->getErrors());
        $this->assertEquals('author_foo', $entity->discriminator);
    }

    public function testFind()
    {
        $authors = TableRegistry::get('Authors', [
            'table' => 'users'
        ]);
        $authors->addBehavior('Robotusers/TableInheritance.Sti');

        $authorResults = $authors->find();
        $this->assertCount(1, $authorResults);

        $editors = TableRegistry::get('Editors', [
            'table' => 'users'
        ]);
        $editors->addBehavior('Robotusers/TableInheritance.Sti');

        $editorResults = $editors->find();
        $this->assertCount(1, $editorResults);

        $subscribers = TableRegistry::get('Subscribers', [
            'table' => 'users'
        ]);
        $subscribers->addBehavior('Robotusers/TableInheritance.Sti');

        $subscriberResults = $subscribers->find();
        $this->assertCount(0, $subscriberResults);
    }

    public function testFindWildcard()
    {
        $authors = TableRegistry::get('Authors', [
            'table' => 'users'
        ]);
        $authors->addBehavior('Robotusers/TableInheritance.Sti', [
            'acceptedDiscriminators' => 'Auth*'
        ]);

        $authorResults = $authors->find();
        $this->assertCount(1, $authorResults);

        $authors->addAcceptedDiscriminator('Edit*');

        $authorResults = $authors->find();
        $this->assertCount(2, $authorResults);
    }

    public function testDelete()
    {
        $table = TableRegistry::get('Authors', [
            'table' => 'users'
        ]);
        $table->addBehavior('Robotusers/TableInheritance.Sti');

        $entity = $table->get(1);
        $deleted = $table->delete($entity);
        $this->assertTrue($deleted);

        $entity = TableRegistry::get('Users')->get(2);
        $deleted = $table->delete($entity);
        $this->assertFalse($deleted);
    }

    public function testDeleteWildcard()
    {
        $table = TableRegistry::get('Authors', [
            'table' => 'users'
        ]);
        $table->addBehavior('Robotusers/TableInheritance.Sti', [
            'acceptedDiscriminators' => 'Auth*'
        ]);

        $entity = $table->get(1);
        $deleted = $table->delete($entity);
        $this->assertTrue($deleted);

        $entity = TableRegistry::get('Users')->get(2);
        $deleted = $table->delete($entity);
        $this->assertFalse($deleted);
    }
}
