<?php
namespace tests\Unit;

use GuardAuthenticator\StaticGuardAuthenticator;
use Manager\AccessDecisionManager;
use Psr\Log\NullLogger;
use Storage\TokenStorage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Guard\Firewall\GuardAuthenticationListener;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Guard\Provider\GuardAuthenticationProvider;

/**
 * GuardTest
 *
 * @author Jean Pasqualini <jpasqualini75@gmail.com>
 * @package tests;
 */
class GuardTest extends \PHPUnit_Framework_TestCase
{
    public function testPhp()
    {
        $anonymousToken = new AnonymousToken('superman', new User('anonymous', ''));

        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($anonymousToken);

        $guardAuthenticators = array(
            new StaticGuardAuthenticator()
        );

        $userProvider = new InMemoryUserProvider(array(
            'john' => array(
                'roles' => array('ROLE_USER'),
                'password' => 'gates'
            )
        ));

        $providerKey = 'minecraft';

        $authentificationManager = new AuthenticationProviderManager(array(
            new GuardAuthenticationProvider(
                $guardAuthenticators,
                $userProvider,
                $providerKey,
                new UserChecker()
            )
        ));

        $authListeners = array(
            new GuardAuthenticationListener(
                new GuardAuthenticatorHandler($tokenStorage),
                $authentificationManager,
                $providerKey,
                $guardAuthenticators,
                new NullLogger()
            )
        );

        // GuardListener est dépendant d'un contexte HTTP :(
        $httpKernel = $this->getMockBuilder(HttpKernel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event = new GetResponseEvent($httpKernel, $request, HttpKernel::MASTER_REQUEST);

        foreach($authListeners as $authListener)
        {
            $authListener->handle($event);
        }

        $authorizationChecker = new AuthorizationChecker(
            $tokenStorage,
            $authentificationManager,
            new AccessDecisionManager()
        );

        $this->assertTrue($authorizationChecker->isGranted('ROLE_USER'));
        $this->assertNotTrue($authorizationChecker->isGranted('ROLE_ADMIN'));
    }
}