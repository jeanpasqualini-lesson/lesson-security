<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/14/15
 * Time: 1:02 AM.
 */

namespace Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Token\LoginPasswordToken;

class ArrayAuthenticationProvider implements AuthenticationProviderInterface
{
    private $users;

    public function __construct(array $users)
    {
        $this->users = $users;
    }

    /**
     * Attempts to authenticate a TokenInterface object.
     *
     * @param TokenInterface $token The TokenInterface instance to authenticate
     *
     * @return TokenInterface An authenticated TokenInterface instance, never null
     *
     * @throws AuthenticationException if the authentication fails
     */
    public function authenticate(TokenInterface $token)
    {
        foreach ($this->users as $username => $info) {
            if ($username === $token->getUsername()) {
                return new LoginPasswordToken($username, "", $info["roles"]);
            }
        }

        // TODO: Implement authenticate() method.
    }

    /**
     * Checks whether this provider supports the given token.
     *
     * @param TokenInterface $token A TokenInterface instance
     *
     * @return bool true if the implementation supports the Token, false otherwise
     */
    public function supports(TokenInterface $token)
    {
        // TODO: Implement supports() method.

        return $token instanceof LoginPasswordToken;
    }
}
