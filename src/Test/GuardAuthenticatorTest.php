<?php
/**
 * Created by PhpStorm.
 * User: aurore
 * Date: 15/10/2016
 * Time: 08:01
 */

namespace Test;


use GuardAuthenticator\StaticGuardAuthenticator;
use Interfaces\TestInterface;
use Manager\AccessDecisionManager;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Guard\Firewall\GuardAuthenticationListener;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Guard\Provider\GuardAuthenticationProvider;

class GuardAuthenticatorTest implements TestInterface
{
    public function runTest()
    {
        $output = new ConsoleOutput();
        $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);

        echo 'guard authenticator test'.PHP_EOL;

        $tokenStorage = new \Storage\TokenStorage(new AnonymousToken("lol", array()));

        $guardAuthenticators = array(
            new StaticGuardAuthenticator()
        );

        $userProvider = new InMemoryUserProvider(array(
            "john" => array(
                "roles" => array("ROLE_USER"),
                "password" => "gates"
            ),
        ));

        $providerKey = 'minecraft';

        $authentificationManager = new AuthenticationProviderManager(array(
            new GuardAuthenticationProvider($guardAuthenticators, $userProvider, $providerKey, new UserChecker())
        ));

        $authListeners = array(
            new GuardAuthenticationListener(
                new GuardAuthenticatorHandler($tokenStorage),
                $authentificationManager,
                $providerKey,
                $guardAuthenticators,
                new ConsoleLogger($output)
            )
        );

        // GuardListener est dépendant d'un contexte HTTP :(
        $httpKernel = new HttpKernel(new EventDispatcher(), new ControllerResolver());
        $request = new Request();
        $event = new GetResponseEvent($httpKernel, $request, HttpKernel::MASTER_REQUEST);

        foreach ($authListeners as $authListener) {
            echo "=== AuthListener : ".get_class($authListener)." ===".PHP_EOL;
            $authListener->handle($event);
        }

        echo PHP_EOL.PHP_EOL;

        if ($tokenStorage->getToken() && $tokenStorage->getToken()->isAuthenticated()) {
            $output->writeln("authenticated succes : ".$tokenStorage->getToken()->getUsername());
        } else {
            $output->writeln("authenticated failled : (token : ".gettype($tokenStorage->getToken()).')');
        }

        $authorizationChecker = new AuthorizationChecker(
            $tokenStorage,
            $authentificationManager,
            new AccessDecisionManager()
        );

        try {
            if ($authorizationChecker->isGranted("ROLE_ADMIN")) {
                $output->writeln("WELCOME IN ADMIN AREA");
            } elseif ($authorizationChecker->isGranted("ROLE_USER")) {
                $output->writeln("WELCOME IN USER AREA");
            } else {
                $output->writeln("WELCOME IN ANONYMOUS AREA");
            }
        } catch (AuthenticationCredentialsNotFoundException $e) {
            $output->writeln("Auth credential not found");
        }
    }
}