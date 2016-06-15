<?php

namespace Robotusers\TableInheritance\Model\Behavior;

/**
 * @author Robert Pustułka robert.pustulka@gmail.com
 * @copyright 2016 RobotUsers
 * @license MIT
 */
trait MatchesTrait
{

    /**
     * Checks rules match.
     *
     * @param type $subject Subject
     * @param array $rules Rules list
     * @return bool
     */
    protected function _matches($subject, array $rules)
    {
        foreach ($rules as $rule) {
            $pattern = '/^' . str_replace('\*', '.*', preg_quote($rule, '/')) . '$/';
            if (preg_match($pattern, $subject)) {
                return true;
            }
        }

        return false;
    }
}
