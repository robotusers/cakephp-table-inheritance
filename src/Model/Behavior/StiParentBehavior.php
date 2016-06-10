<?php

namespace Robotusers\TableInheritance\Model\Behavior;

use ArrayAccess;
use Cake\Database\Query;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Table;
use Robotusers\TableInheritance\Model\Entity\CopyableEntityInterface;

/**
 * @author Robert Pustułka robert.pustulka@gmail.com
 * @copyright 2016 RobotUsers
 * @license MIT
 */
class StiParentBehavior extends Behavior
{

    use LocatorAwareTrait;
    use MatchesTrait;

    /**
     * Defualt options.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'tableMap' => [],
        'discriminatorMap' => [],
        'discriminatorField' => 'discriminator'
    ];

    /**
     * Tables cache.
     *
     * @var array
     */
    protected $_childTables = [];

    /**
     * Gets a STI table.
     *
     * @param string|\Cake\Datasource\EntityInterface $subject Discriminator value or an entity.
     * @return \Cake\ORM\Table
     */
    public function stiTable($subject)
    {
        if (is_array($subject) || $subject instanceof ArrayAccess) {
            $property = $this->_config['discriminatorField'];
            if (isset($subject[$property])) {
                $discriminator = $subject[$property];
            } else {
                return $this->_table;
            }
        } else {
            $discriminator = $subject;
        }

        if (!array_key_exists($discriminator, $this->_childTables)) {
            $table = $this->_findInTableMap($discriminator);

            if (!$table) {
                $table = $this->_findInDiscriminatorMap($discriminator);
            }
            if (!$table) {
                $table = $this->_table;
            }

            $this->addStiTable($discriminator, $table);
        }

        return $this->_childTables[$discriminator];
    }

    /**
     * Adds a table to STI cache.
     *
     * @param string $discriminator Discriminator.
     * @param \Cake\ORM\Table|string|array $table Table instance or alias or config.
     * @return void
     */
    public function addStiTable($discriminator, $table)
    {
        if (!$table instanceof Table) {
            if (is_array($table)) {
                $options = $table;
                $alias = $table['alias'];
            } else {
                $options = [];
                $alias = $table;
            }

            $table = $this->tableLocator()->get($alias, $options);
        }

        $this->_childTables[$discriminator] = $table;
    }

    /**
     * Creates new entity using STI table.
     *
     * @param array $data Data.
     * @param array $options Options.
     * @return \Cake\Datasource\EntityInterface
     */
    public function newStiEntity(array $data = [], array $options = [])
    {
        $table = $this->stiTable($data);

        return $table->newEntity($data, $options);
    }

    /**
     * BeforeFind callback - converts entities based on STI tables.
     *
     * @param \Cake\Event\Event $event Event.
     * @param \Cake\Database\Query $query Query.
     * @param \ArrayAccess $options Options.
     * @return void
     */
    public function beforeFind(Event $event, Query $query, ArrayAccess $options)
    {
        if (!$query->hydrate()) {
            return;
        }
        $query->formatResults(function ($results) {
            return $results->map(function ($row) {
                if ($row instanceof CopyableEntityInterface) {
                    $table = $this->stiTable($row);
                    $entityClass = $table->entityClass();

                    $row = new $entityClass($row->copyProperties(), [
                        'markNew' => $row->isNew(),
                        'markClean' => true,
                        'guard' => false,
                        'source' => $table->registryAlias()
                    ]);
                }

                return $row;
            });
        });
    }

    /**
     * Searches for a match in tableMap.
     *
     * @param string $discriminator Discriminator.
     * @return string
     */
    protected function _findInTableMap($discriminator)
    {
        $map = $this->_config['tableMap'];
        foreach ($map as $table => $rules) {
            if ($this->_matches($discriminator, (array)$rules)) {
                return $table;
            }
        }
    }

    /**
     * Searches for a match in tableMap.
     *
     * @param string $discriminator Discriminator.
     * @return mixed
     */
    protected function _findInDiscriminatorMap($discriminator)
    {
        $map = $this->_config['discriminatorMap'];
        foreach ($map as $rule => $table) {
            if ($this->_matches($discriminator, (array)$rule)) {
                return $table;
            }
        }
    }
}
