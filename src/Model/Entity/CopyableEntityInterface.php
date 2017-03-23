<?php

namespace Robotusers\TableInheritance\Model\Entity;

use Cake\Datasource\EntityInterface;

/**
 * @author Robert Pustułka robert.pustulka@gmail.com
 * @copyright 2016 RobotUsers
 * @license MIT
 */
interface CopyableEntityInterface extends EntityInterface
{

    /**
     *
     * @return array
     */
    public function copyProperties();
}
