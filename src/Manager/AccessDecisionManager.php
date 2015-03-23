<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/23/15
 * Time: 3:10 AM
 */

namespace Manager;


use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * Class AccessDecisionManager
 * @package Manager
 */
class AccessDecisionManager implements AccessDecisionManagerInterface
{
    /**
     * Decides whether the access is possible or not.
     *
     * @param TokenInterface $token      A TokenInterface instance
     * @param array          $attributes An array of attributes associated with the method being invoked
     * @param object         $object     The object to secure
     *
     * @return bool true if the access is granted, false otherwise
     */
    public function decide(TokenInterface $token, array $attributes, $object = null)
    {
        // TODO: Implement decide() method.

        foreach ($token->getRoles() as $role) {
            if (in_array($role->getRole(), $attributes)) {
                return true;
            }
        }
    }

    /**
     * Checks if the access decision manager supports the given attribute.
     *
     * @param string $attribute An attribute
     *
     * @return bool true if this decision manager supports the attribute, false otherwise
     */
    public function supportsAttribute($attribute)
    {
        // TODO: Implement supportsAttribute() method.
    }

    /**
     * Checks if the access decision manager supports the given class.
     *
     * @param string $class A class name
     *
     * @return true if this decision manager can process the class
     */
    public function supportsClass($class)
    {
        // TODO: Implement supportsClass() method.
    }

}