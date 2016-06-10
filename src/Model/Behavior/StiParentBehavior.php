<?php

namespace Robotusers\TableInheritance\Model\Behavior;

use ArrayAccess;
use Cake\Datasource\EntityInterface;
use Cake\Database\Query;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Table;
use Robotusers\TableInheritance\Model\Entity\CopyableEntityInterface;

/**
 * @author Robert Pustułka robert.pustulka@gmail.com
 * @copyright 2015 RobotUsers
 * @license MIT
 */
class StiParentBehavior extends Behavior
{

    use LocatorAwareTrait;

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
     * @param string $discriminator Discriminator.
     * @return \Cake\ORM\Table
     */
    public function stiTable($discriminator)
    {
        if (!array_key_exists($discriminator, $this->_childTables)) {
            $table = $this->config("discriminatorMap.$discriminator");

            if (!$table) {
                $table = $this->_findTableInMap();
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
        $table = $this->_detectTable($data);

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
                    $table = $this->_detectTable($row);
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
     * Detect STI table based on data.
     *
     * @param array $data Data.
     * @return \Cake\ORM\Table
     */
    protected function _detectTable($data)
    {
        $property = $this->_config['discriminatorField'];

        if (!empty($data[$property])) {
            $table = $this->stiTable($data[$property]);
        } else {
            $table = $this->_table;
        }

        return $table;
    }

    /**
     *
     * @return string
     */
    protected function _findTableInMap()
    {
        $map = $this->_config['tableMap'];
        foreach ($map as $table => $discriminators) {
            if (in_array($table, (array)$discriminators)) {
                return $table;
            }
        }
    }
}