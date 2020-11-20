<?php

namespace Robotusers\TableInheritance\Model\Entity;

/**
 * @author Robert Pustułka robert.pustulka@gmail.com
 * @copyright 2016 RobotUsers
 * @license MIT
 */
trait CopyableEntityTrait
{

    /**
     *
     * @return array
     */
    public function copyProperties()
    {
        return $this->_fields;
    }
}
