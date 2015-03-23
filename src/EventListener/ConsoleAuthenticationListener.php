<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/14/15
 * Time: 1:14 AM.
 */

namespace EventListener;

use EntryPoint\ConsoleEntryPoint;
use Event\ConsoleEvent;
use Interfaces\AuthenticationListenerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Token\LoginPasswordToken;

/**
 * Class StaticAuthenticationListener.
 */
class ConsoleAuthenticationListener implements AuthenticationListenerInterface
{
    private $authenticationManager;

    private $tokenStorage;

    private $authenticateEntryPoint;

    /**
     * @param
     * @param string $password
     */
    public function __construct(AuthenticationManagerInterface $authenticationManager, TokenStorageInterface $tokenStorage)
    {
        $this->authenticateEntryPoint = new ConsoleEntryPoint();

        $this->authenticationManager = $authenticationManager;

        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param ConsoleEvent $event
     */
    public function handle(ConsoleEvent $event)
    {
        /** @var OutputInterface $ouput */
        $ouput = $event->getOutput();

        if ($this->tokenStorage->getToken() && $this->tokenStorage->getToken()->isAuthenticated()) {
            return;
        }

        $data = $this->authenticateEntryPoint->start($ouput, null);

        $token = new LoginPasswordToken($data["username"], $data["password"]);

        try {
            $authenticatedToken = $this->authenticationManager->authenticate($token);

            $this->tokenStorage->setToken($authenticatedToken);
        } catch (AuthenticationException $e) {
            $ouput->writeln("authentification failed");
        }
    }
}
