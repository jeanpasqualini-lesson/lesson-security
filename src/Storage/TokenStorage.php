<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/23/15
 * Time: 3:15 AM
 */
namespace Storage;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class TokenStorage
 */
class TokenStorage implements TokenStorageInterface
{
    private $token;

    /**
     * Returns the current security token.
     *
     * @return \Symfony\Component\Security\Core\Authentication\Token\TokenInterface|null A TokenInterface instance or null if no authentication information is available
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Sets the authentication token.
     *
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token A TokenInterface token, or null if no further authentication information should be stored
     */
    public function setToken(\Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token = null)
    {
        $this->token = $token;
    }

}