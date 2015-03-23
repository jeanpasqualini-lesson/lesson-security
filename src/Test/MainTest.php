<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/13/15
 * Time: 11:11 PM.
 */

namespace Test;

use Event\ConsoleEvent;
use EventListener\ConsoleAuthenticationListener;
use EventListener\StaticAuthenticationListener;
use Interfaces\TestInterface;
use Provider\ArrayAuthenticationProvider;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

/**
 * Class MainTest.
 */
class MainTest implements TestInterface, EventSubscriberInterface
{
    /**
     *
     */
    public function runTest()
    {
        $consoleEvent = new ConsoleEvent(new ArgvInput(), new ConsoleOutput());

        $dispatcher = new EventDispatcher();

        $dispatcher->addSubscriber($this);

        $dispatcher->dispatch(ConsoleEvent::EVENT_CONSOLE, $consoleEvent);

        return;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            ConsoleEvent::EVENT_CONSOLE => array("onConsole", 0),
        );
    }

    /**
     * @param ConsoleEvent $event
     */
    public function onConsole(ConsoleEvent $event)
    {
        /*
         * @var OutputInterface
         */
        $output = $event->getOutput();

        $tokenStorage = new \Storage\TokenStorage(new AnonymousToken("lol", array()));

        $authentificationManager = new AuthenticationProviderManager(array(
            new ArrayAuthenticationProvider(array(
                "john" => array(
                    "roles" => array("ROLE_USER"),
                    "password" => "gates"
                ),
            )),
            new ArrayAuthenticationProvider(array(
                "jules" => array(
                    "roles" => array("ROLE_ADMIN"),
                    "password" => "vernes"
                ),
            )),
        ));

        $accessDecisionManager = new \Manager\AccessDecisionManager();

        $authListeners = array(
            new ConsoleAuthenticationListener($authentificationManager, $tokenStorage),
            new StaticAuthenticationListener("jules", "vernes", $authentificationManager, $tokenStorage),
        );

        foreach ($authListeners as $authListener) {
            echo "=== AuthListener : ".get_class($authListener)." ===".PHP_EOL;
            $authListener->handle($event);
        }

        if ($tokenStorage->getToken() && $tokenStorage->getToken()->isAuthenticated()) {
            $output->writeln("authenticated succes : ".$tokenStorage->getToken()->getUsername());
        }

        $authorizationChecker = new AuthorizationChecker(
            $tokenStorage,
            $authentificationManager,
            $accessDecisionManager
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
