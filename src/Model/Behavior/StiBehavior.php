<?php

namespace Robotusers\TableInheritance\Model\Behavior;

use ArrayAccess;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Query;

/**
 * @author Robert Pustułka robert.pustulka@gmail.com
 * @copyright 2015 RobotUsers
 * @license MIT
 */
class StiBehavior extends Behavior
{

    /**
     * Defualt options.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'discriminatorField' => 'discriminator',
        'discriminator' => null,
        'table' => null
    ];

    /**
     * Initialize method.
     *
     * @param array $config
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
     * beforeSave callback.
     *
     * @param \Cake\Event\Event $event
     * @param \Cake\Datasource\EntityInterface $entity
     * @param \ArrayAccess $options
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayAccess $options)
    {
        $discriminator = $this->_discriminator($options);

        if ($discriminator !== false) {
            $entity->set($this->_config['discriminatorField'], $discriminator);
        }
    }

    /**
     * beforeFind callback.
     *
     * @param \Cake\Event\Event $event
     * @param \Cake\ORM\Query $query
     * @param \ArrayAccess $options
     */
    public function beforeFind(Event $event, Query $query, ArrayAccess $options)
    {
        $discriminator = $this->_discriminator($options);

        if ($discriminator !== false) {
            $query->where([
                $this->_table->aliasField($this->_config['discriminatorField']) => $discriminator
            ]);
        }
    }

    /**
     * beforeDelete callback.
     *
     * @param \Cake\Event\Event $event
     * @param \Cake\Datasource\EntityInterface $entity
     * @param \ArrayAccess $options
     */
    public function beforeDelete(Event $event, EntityInterface $entity, ArrayAccess $options)
    {
        $discriminator = $this->_discriminator($options);

        if ($discriminator !== false) {
            $discriminatorField = $this->_config['discriminatorField'];

            if ($entity->has($discriminatorField) && $entity->get($discriminatorField) !== $discriminator) {
                $event->stopPropagation();
                return false;
            }
        }
    }

    /**
     *
     * @param ArrayAccess $options
     * @return string
     */
    protected function _discriminator($options)
    {
        return isset($options['discriminator']) ? $options['discriminator'] : $this->discriminator();
    }
}
