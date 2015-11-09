<?php
/**
 * Created by PhpStorm.
 * User: prestataire
 * Date: 09/11/15
 * Time: 15:11
 */

namespace EventListener;


use Event\ConsoleEvent;
use Interfaces\AuthenticationListenerInterface;
use Token\LoginPasswordToken;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class FileAuthentificationListener implements AuthenticationListenerInterface
{
    private $authenticationManager;

    private $tokenStorage;

    /**
     * @param string                         $username
     * @param string                         $password
     * @param AuthenticationManagerInterface $authenticationManager
     * @param TokenStorageInterface          $tokenStorage
     */
    public function __construct(AuthenticationManagerInterface $authenticationManager, TokenStorageInterface $tokenStorage)
    {
        $this->authenticationManager = $authenticationManager;

        $this->tokenStorage = $tokenStorage;
    }

    public function handle(ConsoleEvent $event)
    {
        /** @var OutputInterface $ouput */
        $output = $event->getOutput();

        $input = $event->getInput();

        if ($this->tokenStorage->getToken() && $this->tokenStorage->getToken()->isAuthenticated()) {
            $output->writeln("[SKIP] Deja authentifier");
            return;
        }

        $filepath = ($input->hasOption("key-file")) ? $input->getOption("key-file") : "auth.key";

        if(file_exists($filepath))
        {
            $data = str_replace(array("\r", "\n"), "", file_get_contents($filepath));

            list($username, $password) = explode(":", $data);

            $output->writeln("file $filepath auth found with username ($username) and password ($password)");

            $token = new LoginPasswordToken($username, $password);

            try {
                $tokenAuthentificated = $this->authenticationManager->authenticate($token);

                $this->tokenStorage->setToken($tokenAuthentificated);
            }
            catch(AuthenticationException $e)
            {
                $output->writeln("authentification failled file : exception");
            }

            return;
        }

        $output->writeln("authentification failled file $filepath not found");
    }
}