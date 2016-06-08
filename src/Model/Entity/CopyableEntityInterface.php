<?php

namespace Robotusers\TableInheritance\Model\Entity;

/**
 * @author Robert Pustułka robert.pustulka@gmail.com
 * @copyright 2016 RobotUsers
 * @license MIT
 */
interface CopyableEntityInterface
{
    
    /**
     *
     * @return array
     */
    public function copyProperties();
}
