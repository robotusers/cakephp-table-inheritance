<?php

namespace Robotusers\TableInheritance\Test\Mock;

use Cake\ORM\Entity;
use Robotusers\TableInheritance\Model\Entity\CopyableEntityInterface;
use Robotusers\TableInheritance\Model\Entity\CopyableEntityTrait;

class User extends Entity implements CopyableEntityInterface
{
    use CopyableEntityTrait;
}
