<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/14/15
 * Time: 1:14 AM.
 */

namespace EventListener;

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
class StaticAuthenticationListener implements AuthenticationListenerInterface
{
    private $username;
    private $password;

    private $authenticationManager;

    private $tokenStorage;

    private $authenticateEntryPoint;

    /**
     * @param string                         $username
     * @param string                         $password
     * @param AuthenticationManagerInterface $authenticationManager
     * @param TokenStorageInterface          $tokenStorage
     */
    public function __construct($username, $password, AuthenticationManagerInterface $authenticationManager, TokenStorageInterface $tokenStorage)
    {
        $this->username = $username;
        $this->password = $password;

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

        $data = array(
            "username" => $this->username,
            "password" => $this->password,
        );

        $token = new LoginPasswordToken($data["username"], $data["password"]);

        try {
            $authenticatedToken = $this->authenticationManager->authenticate($token);

            $this->tokenStorage->setToken($authenticatedToken);
        } catch (AuthenticationException $e) {
            $ouput->writeln("authentification failed");
        }
    }
}
