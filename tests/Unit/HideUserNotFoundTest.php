<?php

namespace tests\Unit;

use Symfony\Component\Security\Core\Authentication\Provider\UserAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

// AuthenticationProvider (support, authenticate, retrieveUser, checkAuthentication)
// UserProvider (loadUserByUsername, refreshUser, supportsClass)

class HideUserNotFoundTest extends \PHPUnit_Framework_TestCase
{
    public function testEnableHideUserNotFound()
    {
        $this->setExpectedException(BadCredentialsException::class);

        $userChecker = $this->createMock(UserCheckerInterface::class);

        $authenticationProvider = new Class($userChecker, $providerKey = 'app', $hideUserNotFound = true) extends UserAuthenticationProvider
        {
            protected function retrieveUser($username, UsernamePasswordToken $token)
            {
                throw new UsernameNotFoundException();
            }

            protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
            {
                return true;
            }
        };

        $token = new UsernamePasswordToken('john.', 'gates', 'app');
        $authenticationProvider->authenticate($token);
    }

    public function testDisabledHideUserNotFound()
    {
        $this->setExpectedException(UsernameNotFoundException::class);

        $userChecker = $this->createMock(UserCheckerInterface::class);

        $authenticationProvider = new Class($userChecker, $providerKey = 'app', $hideUserNotFound = false) extends UserAuthenticationProvider
        {
            protected function retrieveUser($username, UsernamePasswordToken $token)
            {
                throw new UsernameNotFoundException();
            }

            protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
            {
                return true;
            }
        };

        $token = new UsernamePasswordToken('john.', 'gates', 'app');
        $authenticationProvider->authenticate($token);
    }
}