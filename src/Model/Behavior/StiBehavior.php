<?php

namespace Robotusers\TableInheritance\Model\Behavior;

use ArrayAccess;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Query;

class StiBehavior extends Behavior
{
    const OPTION_NAME = 'discriminator';

    /**
     *
     * @var array
     */
    protected $_defaultConfig = [
        'discriminatorField' => static::OPTION_NAME,
        'table' => null
    ];

    /**
     *
     * @param array $config
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->_table->table($this->_config['table']);
    }

    /**
     *
     * @param string $discriminator
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
     * 
     * @param Event $event
     * @param EntityInterface $entity
     * @param ArrayAccess $options
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayAccess $options)
    {
        $discriminator = $this->_discriminator($options);

        if ($discriminator !== false) {
            $entity->set($this->_config['discriminatorField'], $discriminator);
        }
    }

    /**
     *
     * @param Event $event
     * @param Query $query
     * @param ArrayAccess $options
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
     *
     * @param ArrayAccess $options
     * @return string
     */
    protected function _discriminator($options)
    {
        return $options->offsetExists(static::OPTION_NAME) ? $options[static::OPTION_NAME] : $this->discriminator();
    }
}
