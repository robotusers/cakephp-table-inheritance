<?php

namespace Robotusers\TableInheritance\Model\Behavior;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;

/**
 * @author Robert Pustułka robert.pustulka@gmail.com
 * @copyright 2016 RobotUsers
 * @license MIT
 */
class StiBehavior extends Behavior
{
    
    use MatchesTrait;

    /**
     * Default options.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'discriminatorField' => 'discriminator',
        'discriminator' => null,
        'table' => null,
        'checkRules' => true,
        'acceptedDiscriminators' => []
    ];

    /**
     * Discriminator value.
     *
     * @var string
     */
    protected $_discriminator;

    /**
     * Accepted discriminators.
     *
     * @var array
     */
    protected $_acceptedDiscriminators = [];

    /**
     * Initialize method.
     *
     * @param array $config Config.
     * @return void
     */
    public function initialize(array $config)
    {
        if ($this->_config['table'] !== null) {
            $this->_table->table($this->_config['table']);
        }
        if ($this->_config['discriminator'] !== null) {
            $this->discriminator($this->_config['discriminator']);
        }
    }

    /**
     * Accessor/mutator for discriminator value. It's the value used to determine which row belongs to which table.
     *
     * @param string|null $discriminator Discriminator value.
     * @return string
     */
    public function discriminator($discriminator = null)
    {
        if ($discriminator !== null) {
            $this->_discriminator = $discriminator;
        }
        if ($this->_discriminator === null) {
            $this->_discriminator = $this->_table->alias();
        }
        return $this->_discriminator;
    }

    /**
     * Returns accepted discriminators.
     *
     * @return array
     */
    public function acceptedDiscriminators()
    {
        if (!$this->_acceptedDiscriminators) {
            $accepted = $this->_config['acceptedDiscriminators'];
            if (!$accepted) {
                $accepted = $this->discriminator();
            }

            $this->_acceptedDiscriminators = (array)$accepted;
        }

        return $this->_acceptedDiscriminators;
    }

    /**
     * Checks whether a discriminator is accepted.
     *
     * @param string $discriminator Discriminator value.
     * @return bool
     */
    public function isAcceptedDiscriminator($discriminator)
    {
        return $this->_matches($discriminator, $this->acceptedDiscriminators());
    }

    /**
     * Adds an accepted discriminator.
     *
     * @param string $discriminator Discriminator value.
     * @return void
     */
    public function addAcceptedDiscriminator($discriminator)
    {
        $this->_acceptedDiscriminators[] = $discriminator;
    }

    /**
     * buildRules callback.
     *
     * @param \Cake\Event\Event $event Event.
     * @param \Cake\ORM\RulesChecker $rules Rules.
     * @return void
     */
    public function buildRules(Event $event, RulesChecker $rules)
    {
        $discriminator = $this->discriminator();

        if ($this->_config['checkRules'] && $discriminator !== false) {
            $rule = [$this, 'checkRules'];
            $rules->add($rule, 'discriminator', [
                'errorField' => $this->_config['discriminatorField']
            ]);
        }
    }

    /**
     * beforeSave callback.
     *
     * @param \Cake\Event\Event $event Event.
     * @param \Cake\Datasource\EntityInterface $entity Entity.
     * @return void
     */
    public function beforeSave(Event $event, EntityInterface $entity)
    {
        $field = $this->_config['discriminatorField'];
        if ($entity->isNew() && !$entity->has($field)) {
            $discriminator = $this->discriminator();
            $entity->set($field, $discriminator);
        }
    }

    /**
     * beforeFind callback.
     *
     * @param \Cake\Event\Event $event Event
     * @param \Cake\ORM\Query $query Query
     * @return void
     */
    public function beforeFind(Event $event, Query $query)
    {
        $query->where([
            $this->_table->aliasField($this->_config['discriminatorField']) => $this->acceptedDiscriminators()
        ]);
    }

    /**
     * beforeDelete callback.
     *
     * @param \Cake\Event\Event $event Event.
     * @param \Cake\Datasource\EntityInterface $entity Entity.
     * @return bool
     */
    public function beforeDelete(Event $event, EntityInterface $entity)
    {
        $discriminatorField = $this->_config['discriminatorField'];

        if ($entity->has($discriminatorField) && $this->isAcceptedDiscriminator($entity->get($discriminatorField))) {
            $event->stopPropagation();
            return false;
        }
    }

    /**
     * checkRules rule.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity.
     * @return bool
     */
    public function checkRules(EntityInterface $entity)
    {
        $field = $this->_config['discriminatorField'];

        if ($entity->has($field)) {
            return in_array($entity->get($field), $this->acceptedDiscriminators());
        }
    }
}
