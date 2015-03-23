<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/23/15
 * Time: 3:06 AM
 */

namespace Manager;

use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class AuthenticationManager
 * @package Manager
 */
class AuthenticationManager implements AuthenticationManagerInterface
{
    private $providers;

    /**
     * @param array $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
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
        // TODO: Implement authenticate() method.

        foreach ($this->providers as $provider) {
            if (!$provider->supports($token)) {
                continue;
            }

            if (null !== $authenticatedToken = $provider->authenticate($token)) {
                return $authenticatedToken;
            }
        }

        throw new AuthenticationException("You mistype your credentials or have no account");
    }

}